<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'password' => $this->password,
            'telegram' => $this->telegram,
            'role_uuid' => $this->role_uuid,
            'department_uuid' => $this->department_uuid,
            'access_tokens' => UserAccessTokenResource::collection($this->access_tokens),
            'status' => $this->status,
            'last_seen' => $this->last_seen,
        ];
    }
}
