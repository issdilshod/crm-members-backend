<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'chat_uuid' => $this->chat_uuid,
            'user_uuid' => $this->user_uuid,
            'message' => $this->message,
            'created_at' => $this->created_at
        ];
    }
}
