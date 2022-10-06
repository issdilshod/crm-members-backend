<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'company_uuid' => $this->company_uuid,
            'due_date' => $this->due_date,
            'description' => $this->description,
            'priority' => $this->priority,
            'progress' => $this->progress,
            'users' => TaskToUserResourse::collection($this->users),
        ];
    }
}
