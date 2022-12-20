<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyIncorporationResource extends JsonResource
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
            'state_office_website' => $this->state_office_website,
            'incorporation_date' => $this->incorporation_date,
            'annual_report_date' => $this->annual_report_date,
            'registered_agent_exists' => $this->registered_agent_exists,
            'registered_agent_company_name' => $this->registered_agent_company_name,
            'notes' => $this->notes,
            'parent' => $this->parent
        ];
    }
}
