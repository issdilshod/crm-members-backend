<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class FutureCompanyPendingResource extends JsonResource
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
            'name' => $this->db_report_number,
            'address' => [],
            'uploaded_files' => [],
            'last_activity' => $this->last_activity,
            'updated_at' => $this->updated_at,
            'status' => $this->status
        ];
    }
}
