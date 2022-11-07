<?php

namespace App\Services\Chat;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Chat\ChatResource;
use App\Models\Account\Activity;
use App\Models\Chat\Chat;
use Illuminate\Support\Facades\Config;

class ChatService{

    public function all()
    {
        // order by last message
        $chats = Chat::where('status', Config::get('common.status.actived'))
                        ->paginate(20);
        return ChatResource::collection($chats);
    }

    public function one($chat)
    {
        $chat = new ChatResource($chat);
        return $chat;
    }

    public function create($entity)
    {
        $chat = Chat::create($entity);

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

        return new ChatResource($chat);
    }

    public function update(Chat $chat, $entity, $user_uuid)
    {
        $chat = Chat::create($entity);

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

        return new ChatResource($chat);
    }

    public function delete(Chat $chat)
    {
        $chat->update(['status' => Config::get('common.status.deleted')]);
    }

}