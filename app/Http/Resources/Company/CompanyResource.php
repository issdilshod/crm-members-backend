<?php

namespace App\Http\Resources\Company;

use App\Http\Resources\Helper\AddressResource;
use App\Http\Resources\Helper\BankAccountResource;
use App\Http\Resources\Helper\EmailResource;
use App\Http\Resources\Helper\FileResource;
use App\Http\Resources\Helper\FutureWebsiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'legal_name' => $this->legal_name,
            'sic_code_uuid' => $this->sic_code_uuid,
            'director_uuid' => $this->director_uuid,
            'incorporation_date' => $this->incorporation_date,
            'director' => $this->director,
            'incorporation_state_uuid' => $this->incorporation_state_uuid,
            'incorporation_state_name' => $this->incorporation_state_name,
            'doing_business_in_state_uuid' => $this->doing_business_in_state_uuid,
            'doing_business_in_state_name' => $this->doing_business_in_state_name,
            'ein' => $this->ein,
            
            'business_number' => $this->business_number,
            'business_number_type' => $this->business_number_type,
            'voip_provider' => $this->voip_provider,
            'voip_login' => $this->voip_login,
            'voip_password' => $this->voip_password,
            'business_mobile_number' => $this->business_mobile_number,
            'business_mobile_number_type' => $this->business_mobile_number_type,
            'business_mobile_number_provider' => $this->business_mobile_number_provider,
            'business_mobile_number_login' => $this->business_mobile_number_login,
            'business_mobile_number_password' => $this->business_mobile_number_password,

            'website' => $this->website,
            'db_report_number' => $this->db_report_number,

            'future_websites' => FutureWebsiteResource::collection($this->future_websites),

            'bank_account' => BankAccountResource::collection($this->bank_account),
            'address' => AddressResource::collection($this->addresses),
            'emailsdb' => EmailResource::collection($this->emails),
            'uploaded_files' => FileResource::collection($this->files),

            'status' => $this->status
        ];
    }
}
