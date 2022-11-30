<?php

namespace App\Http\Resources\Director;

use App\Http\Resources\Helper\FileResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectorPendingResource extends JsonResource
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
            'name' => $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name,
            'address' => $this->address,
            'uploaded_files' => FileResource::collection($this->files),
            'last_activity' => $this->last_activity,
            'updated_at' => $this->updated_at,
            'status' => $this->status
        ];
    }
}
