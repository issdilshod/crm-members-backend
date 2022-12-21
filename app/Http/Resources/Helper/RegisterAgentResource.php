<?php

namespace App\Http\Resources\Helper;

use Illuminate\Http\Resources\Json\JsonResource;

class RegisterAgentResource extends JsonResource
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
            'company_name' => $this->company_name,
            'name' => $this->name,
            'website' => $this->website,
            'login' => $this->login,
            'password' => $this->password,
            'email' => $this->email,
            'phone' => $this->phone,
            'parent' => $this->parent,
            'status' => $this->status
        ];
    }
}
