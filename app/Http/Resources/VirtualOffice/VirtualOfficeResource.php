<?php

namespace App\Http\Resources\VirtualOffice;

use App\Http\Resources\Helper\AddressResource;
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
            
            'vo_signer_uuid' => $this->vo_signer_uuid,
            'director' => $this->director,
            'vo_provider_name' => $this->vo_provider_name,
            'vo_provider_username' => $this->vo_provider_username,
            'vo_provider_password' => $this->vo_provider_password,
            'vo_website' => $this->vo_website,
            'vo_contact_person_name' => $this->vo_contact_person_name,
            'vo_contact_person_phone_number' => $this->vo_contact_person_phone_number,
            'vo_contact_person_email' => $this->vo_contact_person_email,
            'online_account' => $this->online_account,
            'online_account_username' => $this->online_account_username,
            'online_account_password' => $this->online_account_password,
            'card_on_file' => $this->card_on_file,
            'card_last_four_digit' => $this->card_last_four_digit,
            'card_holder_name' => $this->card_holder_name,
            'monthly_payment_amount' => $this->monthly_payment_amount,

            'agreement_terms' => $this->agreement_terms,
            'deposit' => $this->deposit,
            'registration_fee' => $this->registration_fee,
            'etc' => $this->etc,

            'contract_terms' => $this->contract_terms,
            'contract_terms_notes' => $this->contract_terms_notes,
            'contract_effective_date' => $this->contract_effective_date,

            // addresses
            'addresses' => AddressResource::collection($this->addresses),

            'status' => $this->status
        ];
    }
}
