<?php

namespace App\Http\Resources\FutureWebsite;

use App\Http\Resources\Helper\RejectReasonResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FutureWebsiteResource extends JsonResource
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
            'link' => $this->link,
            'reject_reason' => new RejectReasonResource($this->reject_reason),
            'status' => $this->status
        ];
    }
}
