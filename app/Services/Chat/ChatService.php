<?php

namespace App\Services\Chat;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Chat\ChatResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Chat\Chat;
use Illuminate\Support\Facades\Config;

class ChatService{

    private $chatUserService;

    public function __construct()
    {
        $this->chatUserService = new ChatUserService();
    }

    public function all()
    {
        // order by last message
        $chats = Chat::where('status', Config::get('common.status.actived'))
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
        // get members 
        $entity['members'] = $this->getMembers($entity['entity_uuid']);
        $entity['partner_uuid'] = $entity['entity_uuid'];
        unset($entity['entity_uuid']);

        $chat = Chat::create($entity);

        // add members
        if (isset($entity['members'])){
            $this->addMembers($chat->uuid, $entity['members']);
        }

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $chat['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $chat['name'], Config::get('common.activity.chat.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.chat_add'),
            'status' => Config::get('common.status.actived')
        ]);

        $chat = $this->setChatMembers($chat);
        return new ChatResource($chat);
    }

    public function update(Chat $chat, $entity, $user_uuid)
    {
        $chat = Chat::create($entity);

        // add members
        if (isset($entity['members'])){
            $this->addMembers($chat->uuid, $entity['members']);
        }

        // delete members
        if (isset($entity['members_to_delete'])){
            $this->deleteMembers($chat->uuid, $entity['members_to_delete']);
        }

        // Activity log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $chat['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $chat['name'], Config::get('common.activity.chat.updated')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.chat_update'),
            'status' => Config::get('common.status.actived')
        ]);

        $chat = $this->setChatMembers($chat);
        return new ChatResource($chat);
    }

    public function delete(Chat $chat)
    {
        $chat->update(['status' => Config::get('common.status.deleted')]);
    }

    public function check_exists($user_uuid, $entity_uuid)
    {
        $chat = Chat::select('chats.*')
                        ->join('chat_users', 'chat_users.chat_uuid', '=', 'chats.uuid')
                        ->where(function ($q) use($user_uuid, $entity_uuid) {
                            $q->where('chats.user_uuid', $user_uuid)
                                ->where('chats.partner_uuid', $entity_uuid);
                        })
                        ->orWhere(function ($q) use($user_uuid, $entity_uuid) {
                            $q->where('chats.partner_uuid', $user_uuid)
                                ->where('chats.user_uuid', $entity_uuid);
                        })
                        
                        ->first();
        if ($chat!=null){
            $chat = $this->setChatMembers($chat);
            return new ChatResource($chat);
        }
        return null;
    }

    private function getMembers($entity_uuid)
    {
        $members = [];
        $user = User::where('uuid', $entity_uuid)->first();
        $users = User::select('users.*')
                        ->join('departments', 'departments.uuid', '=', 'users.department_uuid')
                        ->where('departments.uuid', $entity_uuid)
                        ->get();
        if ($user!=null) {
            $members[] = $user['uuid'];
        }

        if ($users!=null){
            foreach($users AS $key => $value):
                $members[] = $value['uuid'];
            endforeach;
        }

        return $members;
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

    private function addMembers($chat_uuid, $members)
    {
        foreach ($members as $key => $value):
            $this->chatUserService->add_user_to_chat($chat_uuid, $value);
        endforeach;
    }

    private function deleteMembers($chat_uuid, $members)
    {
        foreach ($members as $key => $value):
            $this->chatUserService->delete_user_from_chat($chat_uuid, $value);
        endforeach;
    }

}