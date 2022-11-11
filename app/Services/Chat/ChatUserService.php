<?php

namespace App\Services\Chat;

use App\Models\Chat\ChatUser;
use Illuminate\Support\Facades\Config;

class ChatUserService{

    public function chat_members($chat_uuid)
    {
        $chatUsers = ChatUser::select('chat_users.user_uuid', 'users.first_name', 'users.last_name', 'users.last_seen')
                                ->join('users', 'users.uuid', '=', 'chat_users.user_uuid')
                                ->where('chat_users.chat_uuid', $chat_uuid)
                                ->where('chat_users.status', Config::get('common.status.actived'))
                                ->get();
        return $chatUsers;
    }

    public function add_user_to_chat($chat_uuid, $user_uuid)
    {
        $chatUser = ChatUser::where('chat_uuid', $chat_uuid)
                                ->where('user_uuid', $user_uuid)
                                ->first();
        if ($chatUser!=null){
            $chatUser->update(['status' => Config::get('common.status.actived')]);
        }else{
            $chatUser = ChatUser::create([
                'chat_uuid' => $chat_uuid,
                'user_uuid' => $user_uuid
            ]);
        }

        return $chatUser;
    }

    public function delete_user_from_chat($chat_uuid, $user_uuid)
    {
        ChatUser::where('chat_uuid', $chat_uuid)
                    ->where('user_uuid', $user_uuid)
                    ->update(['status' => Config::get('common.status.deleted')]);
    }

}