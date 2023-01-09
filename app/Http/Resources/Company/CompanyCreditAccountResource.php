<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCreditAccountResource extends JsonResource
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
            'entity_uuid' => $this->entity_uuid,
            'is_active' => $this->is_active,
            'name' => $this->name,
            'website' => $this->website,
            'phones' => $this->phones,
            'username' => $this->username,
            'password' => $this->password,
            'status' => $this->status
        ];
    }
}
