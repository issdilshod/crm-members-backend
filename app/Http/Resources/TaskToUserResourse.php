<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskToUserResourse extends JsonResource
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
            'task_uuid' => $this->task_uuid,
            'user_uuid' => $this->user_uuid,
            'department_uuid' => $this->department_uuid,
            'group' => $this->group
        ];
    }
}
