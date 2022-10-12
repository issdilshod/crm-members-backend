<?php

namespace App\Http\Resources\Helper;

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
            'entity_uuid' => $this->entity_uuid,
            'domain' => $this->domain,
            'category' => $this->category
        ];
    }
}
