<?php

namespace App\Services\Chat;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Account\ActivityResource;
use App\Http\Resources\Chat\ChatResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Chat\Chat;
use App\Services\Helper\NotificationService;
use Illuminate\Support\Facades\Config;

class ChatService{

    private $chatUserService;
    private $messageService;
    private $notificationService;

    public function __construct()
    {
        $this->chatUserService = new ChatUserService();
        $this->messageService = new MessageService();
        $this->notificationService = new NotificationService();
    }

    public function all()
    {
        // order by last message
        $chats = Chat::select('chats.*')
                        ->where('chats.status', Config::get('common.status.actived'))
                        ->paginate(20);
        $chats = $this->setChatsMembers($chats);
        return ChatResource::collection($chats);
    }

    public function by_user($user_uuid)
    {
        // order by last message
        $chats = Chat::select('chats.*')
                        ->join('chat_users', 'chat_users.chat_uuid', '=', 'chats.uuid')
                        ->where('chat_users.user_uuid', $user_uuid)
                        ->where('chat_users.status', Config::get('common.status.actived'))
                        ->where('chats.status', Config::get('common.status.actived'))
                        ->paginate(20);
        $chats = $this->setChatsMembers($chats);
        return ChatResource::collection($chats);
    }

    public function one($chat)
    {
        $chat = $this->setChatMembers($chat);
        $chat = new ChatResource($chat);
        return $chat;
    }

    public function create($entity)
    {
        $chat = Chat::create($entity);

        // add personal user
        $this->chatUserService->add_user_to_chat($chat->uuid, $entity['user_uuid']);

        // add members
        if (isset($entity['members'])){
            foreach ($entity['members'] AS $key => $value):
                $this->chatUserService->add_user_to_chat($chat->uuid, $value['uuid']);
            endforeach;
        }

        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $chat['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $chat['name'], Config::get('common.activity.chat.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.chat_add'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        $chat = $this->setChatMembers($chat);
        return new ChatResource($chat);
    }

    public function update(Chat $chat, $entity, $user_uuid)
    {
        $chat = Chat::create($entity);

        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $chat['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $chat['name'], Config::get('common.activity.chat.updated')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.chat_update'),
            'status' => Config::get('common.status.actived')
        ]);

         // push
         $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        $chat = $this->setChatMembers($chat);
        return new ChatResource($chat);
    }

    public function delete(Chat $chat)
    {
        $chat->update(['status' => Config::get('common.status.deleted')]);
    }

    public function check_exists($user_uuid, $entity)
    {
        // if chat is group
        if ($entity['type']==Config::get('common.chat.type.group')) { return null; } 

        // check for exists
        if (isset($entity['members'])){
            $chat = Chat::select('chats.*')
                            ->leftJoin('chat_users', 'chat_users.chat_uuid', '=', 'chats.uuid')
                            ->where(function ($q) use($user_uuid, $entity) {
                                $q->where('chats.user_uuid', $user_uuid)
                                    ->where('chats.partner_uuid', $entity['members'][0]['uuid']);
                            })
                            ->orWhere(function ($q) use($user_uuid, $entity) {
                                $q->where('chats.partner_uuid', $user_uuid)
                                    ->where('chats.user_uuid', $entity['members'][0]['uuid']);
                            })
                            ->first();
            if ($chat!=null){
                $chat = $this->setChatMembers($chat);
                return new ChatResource($chat);
            }
        }

        return null;
    }

    private function setChatMembers($chat)
    {
        $chat['members'] = $this->chatUserService->chat_members($chat->uuid);
        return $chat;
    }

    private function setChatsMembers($chats)
    {
        foreach($chats AS $key => $value):
            $chats[$key]['members'] = $this->chatUserService->chat_members($value['uuid']);
        endforeach;
        return $chats;
    }

}