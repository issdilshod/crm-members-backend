<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyBusinessMobileResource extends JsonResource
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
            'business_mobile_number' => $this->business_mobile_number,
            'business_mobile_provider' => $this->business_mobile_provider,
            'business_mobile_website' => $this->business_mobile_website,
            'business_mobile_login' => $this->business_mobile_login,
            'business_mobile_password' => $this->business_mobile_password,
            'card_on_file' => $this->card_on_file,
            'card_last_four_digit' => $this->card_last_four_digit,
            'card_holder_name' => $this->card_holder_name,
            'parent' => $this->parent
        ];
    }
}
