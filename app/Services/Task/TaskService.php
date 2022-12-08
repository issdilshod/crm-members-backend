<?php

namespace App\Services\Task;

use App\Http\Resources\Task\TaskResource;
use App\Models\Account\User;
use App\Models\Task\Task;
use App\Models\Task\TaskToUser;
use App\Services\Helper\NotificationService;
use Illuminate\Support\Facades\Config;

class TaskService {

    private $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function all($user_uuid = '')
    {
        $user = null;
        if ($user_uuid!=''){
            $user = User::where('user_uuid', $user_uuid)->first();
        }

        $tasks = Task::where('status', Config::get('common.status.actived'))
                    ->when(($user!=null), function($q) use($user){
                        return $q->where('executor_user_uuid', $user->uuid)
                                ->orWhere('department_uuid', $user->department_uuid)
                                ->orWhere('user_uuid', $user->uuid);
                    })
                    ->orderBy('updated_at')
                    ->paginate(20);

        return TaskResource::collection($tasks);
    }

    public function one(Task $task)
    {
        return new TaskResource($task);
    }

    public function create($entity)
    {
        $task = Task::create($entity);

        // detect if group
        if (isset($entity['users'])){
            foreach ($entity['users'] AS $key => $value):
                $this->add_user_to_task($task->uuid, $value, false);
            endforeach;
        }else{
            $users = User::where('department_uuid', $entity['department_uuid'])->get();
            foreach ($users->toArray() AS $key => $value):
                $this->add_user_to_task($task->uuid, $value['uuid'], true);
            endforeach;
        }

        // activity

        // notification

        return new TaskResource($task);
    }

    public function update(Task $task, $entity, $user_uuid)
    {
        $task = Task::create($entity);

        // detect if group
        if (isset($entity['users'])){
            foreach ($entity['users'] AS $key => $value):
                $this->add_user_to_task($task->uuid, $value, false);
            endforeach;
        }else{
            $users = User::where('department_uuid', $entity['department_uuid'])->get();
            foreach ($users->toArray() AS $key => $value):
                $this->add_user_to_task($task->uuid, $value['uuid'], true);
            endforeach;
        }

        // delete users
        if (isset($entity['users_to_delete'])){
            foreach ($entity['users_to_delete'] AS $key => $value):
                $this->remove_user_from_task($task->uuid, $value);
            endforeach;
        }

        // activity

        // notification

        return new TaskResource($task);
    }

    public function delete(Task $task)
    {
        $task->update(['status' => Config::get('common.status.deleted')]);
    }

    private function add_user_to_task($task_uuid, $user_uuid, $is_group  = false)
    {
        $taskToUser = TaskToUser::where('task_uuid', $task_uuid)
                                ->where('user_uuid', $user_uuid)
                                ->first();
        if ($taskToUser==null){
            TaskToUser::create([
                'task_uuid' => $task_uuid,
                'user_uuid' => $user_uuid,
                'is_group' => $is_group
            ]);
        }else{
            $taskToUser->update(['status' => Config::get('common.status.actived')]);
        }
    }

    private function remove_user_from_task($task_uuid, $user_uuid)
    {
        TaskToUser::where('task_uuid', $task_uuid)
                    ->where('user_uuid', $user_uuid)
                    ->update(['status' => Config::where('common.status.deleted')]);
    }


}