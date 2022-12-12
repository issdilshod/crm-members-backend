<?php

namespace App\Services\Task;

use App\Http\Resources\Task\TaskCommentResource;
use App\Models\Task\TaskComment;
use Illuminate\Support\Facades\Config;

class TaskCommentService{

    public function all($taskUuid)
    {
        $taskComments = TaskComment::orderBy('created_at', 'DESC')
                                    ->where('task_uuid', $taskUuid)
                                    ->where('status', Config::get('common.status.actived'))
                                    ->paginate(20);
        return TaskCommentResource::collection($taskComments);
    }

    public function add($entity)
    {
        $task = TaskComment::create($entity);

        return new TaskCommentResource($task);
    }

    public function remove()
    {

    }
}