<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
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
            'name' => $this->name,
            'website' => $this->website,
            'username' => $this->username,
            'password' => $this->password,
            'account_number' => $this->account_number,
            'rounting_number' => $this->routing_number,
            'bank_account_security' => BankAccountSecurityResource::collection($this->bank_account_security)
        ];
    }
}
