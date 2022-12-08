<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskCommentResource extends JsonResource
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
            'task_uuid' => $this->task_uuid,
            'comment' => $this->comment,
            'status' => $this->status,
            'updated_at' => $this->updated_at
        ];
    }
}
