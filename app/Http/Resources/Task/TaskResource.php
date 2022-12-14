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
            'task_name' => $this->task_name,
            'department_uuid' => $this->department_uuid,
            'users' => TaskToUserResource::collection($this->users),
            'due_date' => $this->due_date,
            'description' => $this->description,
            'priority' => $this->priority,
            'progress' => $this->progress,
            'status' => $this->status,
            'updated_at' => $this->updated_at
        ];
    }
}
