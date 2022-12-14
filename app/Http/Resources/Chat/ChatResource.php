<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'user_uuid' => $this->user_uuid,
            'user' => $this->user,
            'name' => $this->name,
            'type' => $this->type,
            'members' => (isset($this->members)?$this->members:[]),
            'last_message' => $this->last_message(),
            'unread_count' => 0,
            'created_at' => $this->created_at
        ];
    }
}
