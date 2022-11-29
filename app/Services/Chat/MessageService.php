<?php

namespace App\Services\Chat;

use App\Http\Resources\Chat\MessageResource;
use App\Logs\TelegramLog;
use App\Models\Account\User;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatUser;
use App\Models\Chat\Message;
use App\Services\Helper\NotificationService;
use Illuminate\Support\Facades\Config;

class MessageService {

    private $notifiactionService;

    public function __construct()
    {
        $this->notifiactionService = new NotificationService();
    }

    public function chat_messages($chat_uuid)
    {
        $messages = Message::orderBy('created_at', 'DESC')
                            ->where('status', '!=', Config::get('common.status.deleted'))
                            ->where('chat_uuid', $chat_uuid)
                            ->paginate(20);
        return MessageResource::collection($messages);
    }

    public function one(Message $message)
    {
        $message = new MessageResource($message);
        return $message;
    }

    public function create($entity)
    {
        $message = Message::create($entity);
        return new MessageResource($message);
    }

    public function update(Message $message, $entity)
    {
        $message->update($entity);
        return new MessageResource($message);
    }

    public function delete(Message $message)
    {
        $message->update(['status' => Config::get('common.status.deleted')]);
    }

    public function last_message($chat_uuid)
    {
        $message = Message::select('users.first_name', 'users.last_name', 'messages.message', 'messages.created_at')
                            ->orderBy('messages.created_at', 'DESC')
                            ->leftJoin('users', 'users.uuid', '=', 'messages.user_uuid')
                            ->where('messages.status', Config::get('common.status.actived'))
                            ->where('messages.chat_uuid', $chat_uuid)
                            ->limit(1)
                            ->get();
        if ($message!=null){
            return $message->toArray();
        }
        return ['message' => '', 'created_at' => '', 'first_name' => '', 'last_name' => ''];
                            
    }

    public function send_push($user_uuid, $chat_uuid, $message)
    {
        $chatUsers = ChatUser::where('chat_uuid', $chat_uuid)
                            ->where('user_uuid', '!=', $user_uuid)
                            ->where('status', Config::get('common.status.actived'))
                            ->get(['user_uuid']);

        $chatAuthor = Chat::where('uuid', $chat_uuid)
                            ->first(['user_uuid']);

        $chatUsers = array_merge($chatUsers->toArray(), [$chatAuthor->toArray()]);

        $log = new TelegramLog();
        $log->to_file($chatUsers);

        $author = User::where('uuid', $user_uuid)->first();

        foreach ($chatUsers as $key => $value):

            if ($user_uuid==$value['user_uuid']){continue;}

            $user = User::where('uuid', $value['user_uuid'])->first();

            // if offline to telegram
            if ($user->last_seen!=null){ // online
                $this->notifiactionService->telegram([
                    'telegram'=> $user->telegram, 
                    'msg' => '*' . $author->first_name . ' ' . $author->last_name . "*\n" . "Sent a message: \n" . '_' . $message->message . '_'
                ]);
            }

            // push
            $this->notifiactionService->push('chat', $user, ['link'=>'', 'msg' => '', 'data' => $message]);

        endforeach;
    }

}