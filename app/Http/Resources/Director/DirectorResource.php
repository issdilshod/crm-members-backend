<?php

namespace App\Http\Resources\Director;

use App\Http\Resources\Account\ActivityResource;
use App\Http\Resources\Helper\AddressResource;
use App\Http\Resources\Helper\EmailResource;
use App\Http\Resources\Helper\FileResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectorResource extends JsonResource
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
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,
            'ssn_cpn' => $this->ssn_cpn,
            'company_association' => $this->company_association,
            'phone_type' => $this->phone_type,
            'phone_number' => $this->phone_number,
            'uploaded_files' => FileResource::collection($this->files),
            'emails' => EmailResource::collection($this->emails),
            'address' => AddressResource::collection($this->addresses),
            'last_activity' => new ActivityResource($this->last_activity),
            'status' => $this->status
        ];
    }
}
