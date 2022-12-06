<?php

namespace App\Http\Resources\VirtualOffice;

use Illuminate\Http\Resources\Json\JsonResource;

class VirtualOfficePendingResource extends JsonResource
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
            'name' => $this->vo_provider_name,
            'address' => [],
            'uploaded_files' => [],
            'last_activity' => $this->last_activity,
            'updated_at' => $this->updated_at,
            'status' => $this->status
        ];
    }
}
