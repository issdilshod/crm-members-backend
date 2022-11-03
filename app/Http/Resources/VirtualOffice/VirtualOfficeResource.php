<?php

namespace App\Http\Resources\VirtualOffice;

use Illuminate\Http\Resources\Json\JsonResource;

class VirtualOfficeResource extends JsonResource
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
            'vo_provider_name' => $this->vo_provider_name,
            // address
            'street_address' => $this->street_address,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal' => $this->postal,
            'country' => $this->country,

            'vo_provider_domain' => $this->vo_provider_domain,
            'vo_provider_username' => $this->vo_provider_username,
            'vo_provider_password' => $this->vo_provider_password,
            'status' => $this->status
        ];
    }
}
