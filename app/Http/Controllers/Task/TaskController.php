<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task\Task;
use App\Policies\PermissionPolicy;
use App\Services\Task\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class TaskController extends Controller
{

    private $taskService;

    public function __construct()
    {
        $this->taskService = new TaskService();
    }

    /**     @OA\GET(
      *         path="/api/task",
      *         operationId="list_task",
      *         tags={"Task"},
      *         summary="List of task",
      *         description="List of task",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        $user_uuid = '';

        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            $user_uuid = $request->user_uuid;
        }

        $tasks = $this->taskService->all($user_uuid);
        return $tasks;
    }

    /**     @OA\GET(
      *         path="/api/task/{uuid}",
      *         operationId="get_task",
      *         tags={"Task"},
      *         summary="Get task",
      *         description="Get task",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="task uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Request $request, Task $task)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if ($task->user_uuid!=$request->user_uuid){ // task not created by user
                return response()->json(['status' => 'error', 'msg' => 'not permitted'], 403);
            }
        }

        $task = $this->taskService->one($task);
        return response()->json(['status' => 'ok', 'msg' => 'success', 'data' => $task], 200);
    }

    /**     @OA\POST(
      *         path="/api/task",
      *         operationId="post_task",
      *         tags={"Task"},
      *         summary="Add task",
      *         description="Add task",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"task_name", "department_uuid", "priority", "due_date"},
      *
      *                         @OA\Property(property="task_name", type="text"),
      *                         @OA\Property(property="department_uuid", type="text"),
      *                         @OA\Property(property="priority", type="text"),
      *                         @OA\Property(property="users[]", type="text"),
      *                         @OA\Property(property="due_date", type="text"),
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function store(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.task.store'))){
            return response()->json(['status' => 'error', 'msg' => 'not permitted'], 403);
        }

        $validated = $request->validate([
            'task_name' => 'required',
            'department_uuid' => 'required',
            'users' => 'array',
            'due_date' => 'required',
            'description' => '',
            'priority' => 'required',
            'user_uuid' => ''
        ]);

        $task = $this->taskService->create($validated);
        return response()->json(['status' => 'ok', 'msg' => 'success', 'data' => $task], 201);
    }

    /**     @OA\PUT(
      *         path="/api/task/{uuid}",
      *         operationId="update_task",
      *         tags={"Task"},
      *         summary="Update task",
      *         description="Update task",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="task uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"task_name", "department_uuid", "priority", "due_date"},
      *
      *                         @OA\Property(property="task_name", type="text"),
      *                         @OA\Property(property="department_uuid", type="text"),
      *                         @OA\Property(property="priority", type="text"),
      *                         @OA\Property(property="users[]", type="text"),
      *                         @OA\Property(property="users_to_delete[]", type="text"),
      *                         @OA\Property(property="due_date", type="text"),
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function update(Request $request, Task $task)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // not headquarters
            if ($task->user_uuid!=$request->user_uuid){ // not belong to user
                return response()->json(['status' => 'error', 'msg' => 'not permitted'], 403);
            }
        }

        $validated = $request->validate([
            'task_name' => 'required',
            'department_uuid' => 'required',
            'users' => 'array',
            'users_to_delete' => 'array',
            'due_date' => 'required',
            'description' => '',
            'priority' => 'required'
        ]);

        $task = $this->taskService->update($task, $validated, $request->user_uuid);
        return response()->json(['status' => 'ok', 'msg' => 'success', 'data' => $task], 200);
    }

    /**     @OA\DELETE(
      *         path="/api/task/{uuid}",
      *         operationId="delete_task",
      *         tags={"Task"},
      *         summary="Delete task",
      *         description="Delete task",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="task uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, Task $task)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if ($task->user_uuid!=$request->user_uuid){ // task not created by user
                return response()->json(['status' => 'error', 'msg' => 'not permitted'], 403);
            }
        }

        $this->taskService->delete($task);
        return response()->json(['status' => 'ok', 'msg' => 'success'], 200);
    }

}
