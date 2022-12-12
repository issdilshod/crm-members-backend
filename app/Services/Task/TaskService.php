<?php

namespace App\Services\Task;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Account\ActivityResource;
use App\Http\Resources\Task\TaskResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Task\Task;
use App\Models\Task\TaskToUser;
use App\Services\Account\ActivityService;
use App\Services\Helper\NotificationService;
use Illuminate\Support\Facades\Config;

class TaskService {

    private $notificationService;
    private $activityService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->activityService = new ActivityService();
    }

    public function all($user_uuid = '')
    {
        $tasks = Task::select('tasks.*')
                    ->where('tasks.status', '!=', Config::get('common.status.deleted'))
                    ->leftJoin('task_to_users', 'task_to_users.task_uuid', '=', 'tasks.uuid')
                    ->when(($user_uuid!=''), function($q) use($user_uuid){
                        return $q->where('task_to_users.user_uuid', $user_uuid)
                                    ->where('task_to_users.status', Config::get('common.status.actived'));
                    })
                    ->groupBy('tasks.uuid')
                    ->orderBy('tasks.updated_at', 'DESC')
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

        $notifUser = [];
        // detect if group
        if (isset($entity['users'])){
            foreach ($entity['users'] AS $key => $value):
                $user = User::where('uuid', $value['user_uuid'])->first();
                $this->add_user_to_task($task->uuid, $value['user_uuid'], false);
                $notifUser[] = $user;
            endforeach;
        }else{
            $users = User::where('department_uuid', $entity['department_uuid'])->get();
            foreach ($users->toArray() AS $key => $value):
                $this->add_user_to_task($task->uuid, $value['uuid'], true);
                $notifUser[] = $value;
            endforeach;
        }

        $task = new TaskResource($task);

        // activity
        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $task['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $task['task_name'], Config::get('common.activity.task.add')),
            'changes' => json_encode(new TaskResource($task)),
            'action_code' => Config::get('common.activity.codes.task_add'),
            'status' => Config::get('common.status.actived')
        ]);

        // headquarters activity & task
        $activity = $this->activityService->setLink($activity);
        $activity = new ActivityResource($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => $activity, 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('task', ['data' => $task, 'msg' => '', 'link' => '']);

        // telegram & push users task
        foreach ($notifUser AS $key => $value):
            $this->notificationService->telegram([
                'telegram' => $value['telegram'],
                'msg' => str_replace("{name}", "*" . $task['task_name'] . "*", Config::get('common.activity.task.add')) . "\n" .
                '[link to view]('.env('APP_FRONTEND_ENDPOINT').'?section=task&uuid='.$task['uuid'].')'
            ]);

            $this->notificationService->push('task', $value, ['data' => $task, 'msg' => '', 'link' => '']);
        endforeach;
        
        return $task;
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

    public function to_progress(Task $task, $entity)
    {
        $task->update(['progress' => $entity['progress']]);

        $task = new TaskResource($task);

        // activity
        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $task['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $task['task_name'], Config::get('common.activity.task.to_progress')),
            'changes' => json_encode(new TaskResource($task)),
            'action_code' => Config::get('common.activity.codes.task_to_progress'),
            'status' => Config::get('common.status.actived')
        ]);

        // headquarters activity & task PUSH & TELEGRAM
        $activity = $this->activityService->setLink($activity);
        $activity = new ActivityResource($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => $activity, 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('task', ['data' => $task, 'msg' => '', 'link' => '']);

        $msg = str_replace("{name}", "*" . $task['task_name'] . "*", Config::get('common.activity.task.to_progress')) . "\n" .'[link to view]('.env('APP_FRONTEND_ENDPOINT').'?section=task&uuid='.$task['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // telegram & push users task
        $notifUser = TaskToUser::select('users.*')
                                ->join('users', 'users.uuid', '=', 'task_to_users.user_uuid')
                                ->where('task_to_users.task_uuid', $task['uuid'])
                                ->where('task_to_users.status', Config::get('common.status.actived'))
                                ->get();
        foreach ($notifUser->toArray() AS $key => $value):
            $this->notificationService->push('task', $value, ['data' => $task, 'msg' => '', 'link' => '']);

            if ($entity['user_uuid']==$value['uuid']){ continue; }

            $this->notificationService->telegram([
                'telegram' => $value['telegram'],
                'msg' => str_replace("{name}", "*" . $task['task_name'] . "*", Config::get('common.activity.task.to_progress')) . "\n" .
                '[link to view]('.env('APP_FRONTEND_ENDPOINT').'?section=task&uuid='.$task['uuid'].')'
            ]);
        endforeach;

        return $task;
    }

    public function approve($taskUuid, $userUuid)
    {
        $task = Task::where('uuid', $taskUuid)->first();
        $task->update(['progress' => Config::get('common.task_progress.completed')]);

        $task = new TaskResource($task);

        // activity
        $activity = Activity::create([
            'user_uuid' => $userUuid,
            'entity_uuid' => $task['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $task['task_name'], Config::get('common.activity.task.approve')),
            'changes' => json_encode(new TaskResource($task)),
            'action_code' => Config::get('common.activity.codes.task_approve'),
            'status' => Config::get('common.status.actived')
        ]);

        // headquarters activity & task PUSH & TELEGRAM
        $activity = $this->activityService->setLink($activity);
        $activity = new ActivityResource($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => $activity, 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('task', ['data' => $task, 'msg' => '', 'link' => '']);

        // telegram & push users task
        $notifUser = TaskToUser::select('users.*')
                                ->join('users', 'users.uuid', '=', 'task_to_users.user_uuid')
                                ->where('task_to_users.task_uuid', $task['uuid'])
                                ->where('task_to_users.status', Config::get('common.status.actived'))
                                ->get();
        foreach ($notifUser->toArray() AS $key => $value):
            $this->notificationService->push('task', $value, ['data' => $task, 'msg' => '', 'link' => '']);

            $this->notificationService->telegram([
                'telegram' => $value['telegram'],
                'msg' => str_replace("{name}", "*" . $task['task_name'] . "*", Config::get('common.activity.task.approve')) . "\n" .
                '[link to view]('.env('APP_FRONTEND_ENDPOINT').'?section=task&uuid='.$task['uuid'].')'
            ]);
        endforeach;

    }

    public function reject($taskUuid, $userUuid)
    {
        $task = Task::where('uuid', $taskUuid)->first();
        $task->update(['progress' => Config::get('common.task_progress.rejected')]);

        $task = new TaskResource($task);

        // activity
        $activity = Activity::create([
            'user_uuid' => $userUuid,
            'entity_uuid' => $task['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $task['task_name'], Config::get('common.activity.task.reject')),
            'changes' => json_encode(new TaskResource($task)),
            'action_code' => Config::get('common.activity.codes.task_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // headquarters activity & task PUSH & TELEGRAM
        $activity = $this->activityService->setLink($activity);
        $activity = new ActivityResource($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => $activity, 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('task', ['data' => $task, 'msg' => '', 'link' => '']);

        // telegram & push users task
        $notifUser = TaskToUser::select('users.*')
                                ->join('users', 'users.uuid', '=', 'task_to_users.user_uuid')
                                ->where('task_to_users.task_uuid', $task['uuid'])
                                ->where('task_to_users.status', Config::get('common.status.actived'))
                                ->get();
        foreach ($notifUser->toArray() AS $key => $value):
            $this->notificationService->push('task', $value, ['data' => $task, 'msg' => '', 'link' => '']);

            $this->notificationService->telegram([
                'telegram' => $value['telegram'],
                'msg' => str_replace("{name}", "*" . $task['task_name'] . "*", Config::get('common.activity.task.reject')) . "\n" .
                '[link to view]('.env('APP_FRONTEND_ENDPOINT').'?section=task&uuid='.$task['uuid'].')'
            ]);
        endforeach;
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