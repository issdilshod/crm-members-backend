<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'user' => $this->user,
            'entity_uuid' => $this->entity_uuid,
            'device' => $this->device,
            'ip' => $this->ip,
            'description' => $this->description,
            'updated_at' => $this->updated_at,
            'link' => $this->link,
            'status' => $this->status
        ];
    }
}
