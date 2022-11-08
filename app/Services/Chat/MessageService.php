<?php

namespace App\Services\Chat;

use App\Http\Resources\Chat\MessageResource;
use App\Models\Chat\Message;
use Illuminate\Support\Facades\Config;

class MessageService {

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

}