<?php

namespace App\Http\Resources\Company;

use App\Http\Resources\Director\DirectorResource;
use App\Http\Resources\Helper\FileResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FutureCompanyResource extends JsonResource
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
            'sic_code_uuid' => $this->sic_code_uuid,
            'incorporation_state_uuid' => $this->incorporation_state_uuid,
            'doing_business_in_state_uuid' => $this->doing_business_in_state_uuid,
            'virtual_office_uuid' => $this->virtual_office_uuid,
            'revival_date' => $this->revival_date,
            'revival_fee' => $this->revival_fee,
            'recommended_director_uuid' => $this->recommended_director_uuid,
            'director' => new DirectorResource($this->director),
            'revived' => $this->revived,
            'db_report_number' => $this->db_report_number,
            'comment' => $this->comment,

            'uploaded_files' => FileResource::collection($this->files),
            'status' => $this->status
        ];
    }
}
