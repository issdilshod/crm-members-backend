<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\Task\TaskResource;
use App\Models\Helper\File;
use App\Models\Task\Task;
use App\Models\Task\TaskToUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    /**     @OA\GET(
      *         path="/api/task",
      *         operationId="list_task",
      *         tags={"Task"},
      *         summary="List of task",
      *         description="List of task",
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index()
    {
        $task = Task::where('status', Config::get('common.status.actived'))
                        ->paginate(20);
        return TaskResource::collection($task);
    }

    /**     @OA\POST(
      *         path="/api/task",
      *         operationId="post_task",
      *         tags={"Task"},
      *         summary="Add task (not working on swagger)",
      *         description="Add task",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"user_uuid", "company_uuid", "due_date", "description", "priority", "department_uuid"},
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="company_uuid", type="text"),
      *                         @OA\Property(property="due_date", type="date"),
      *                         @OA\Property(property="description", type="text"),
      *                         @OA\Property(property="priority", type="integer"),
      *                         @OA\Property(property="department_uuid", type="string"),
      *
      *                         @OA\Property(property="files[attachment][]", type="file", format="binary")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function store(Request $request)
    {
        #region Validate

        $validated = $request->validate([
            'user_uuid' => 'required|string',
            'company_uuid' => 'required|string',
            'due_date' => 'required|date',
            'description' => 'required|string|max:2000',
            'priority' => 'required|integer',
            'department_uuid' => 'required|string',

            // users
            'users' => 'array',

            // files
            'files' => 'array'
        ]);

        #endregion

        #region Check exsist models

        $result_check = [];
        // Check Email
        /*$result_check['task'] = Email::where('email', $value['email'])
                                        ->where('hosting_uuid', $value['hosting_uuid'])
                                        ->orWhere('phone', $value['phone'])
                                        ->first();*/

        $exsist = false;
        foreach ($result_check AS $key => $value):
            if ($value != null){
                $exsist = true;
                break;
            }
        endforeach;

        if ($exsist){
            return response()->json([
                        'data' => $result_check,
                    ], 409);
        }

        #endregion

        $task = Task::create($validated);

        #region Users add (if exsist)

        if (isset($validated['users'])){
            foreach($validated['users'] AS $key => $value):
                $task_to_user = new TaskToUser();
                $task_to_user->user_uuid = $value;
                $task_to_user->task_uuid = $task['uuid'];
                $task_to_user->department_uuid = $validated['department_uuid'];
                $task_to_user->group = 0;
                $task_to_user->save();
            endforeach;
        }else{
            $task_to_user = new TaskToUser();
            $task_to_user->task_uuid = $task['uuid'];
            $task_to_user->department_uuid = $validated['department_uuid'];
            $task_to_user->group = 1;
            $task_to_user->save();
        }

        #endregion

        #region Files add (if exsist)

        if ($request->has('files')){
            $files = $request->file('files');
            foreach ($files AS $key => $value):
                $tmp_file = $value;
                $file_parent = $key;

                foreach ($tmp_file AS $key2 => $value2):
                    $file = new File();
                    $file->user_uuid = $validated['user_uuid'];
                    $file->entity_uuid = $task['uuid'];
                    $file->file_name = Str::uuid()->toString() . '.' . $value2->getClientOriginalExtension();
                    $file->file_path = $file->file_name;
                    $file->file_parent = $file_parent;
                    $value2->move('uploads', $file->file_path);
                    $file->save();
                endforeach;
            endforeach;
        }

        #endregion

        return new TaskResource($task);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Task $task)
    {
        return new TaskResource($task);
    }

    /**     @OA\PUT(
      *         path="/api/task",
      *         operationId="update_task",
      *         tags={"Task"},
      *         summary="Update task (not working on swagger)",
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
      *                         required={},
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="company_uuid", type="text"),
      *                         @OA\Property(property="due_date", type="date"),
      *                         @OA\Property(property="description", type="text"),
      *                         @OA\Property(property="priority", type="integer"),
      *                         @OA\Property(property="department_uuid", type="string"),
      *                         @OA\Property(property="users[]", type="string"),
      *                         @OA\Property(property="users_to_delete[]", type="string"),
      *
      *                         @OA\Property(property="files_to_delete[]", type="string"),
      *                         @OA\Property(property="files[attachment][]", type="file", format="binary")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *     )
      */
    public function update(Request $request, Task $task)
    {
        #region Validate

        $validated = $request->validate([
            'user_uuid' => 'string',
            'company_uuid' => 'string',
            'due_date' => 'date',
            'description' => 'string|max:2000',
            'priority' => 'integer',
            'department_uuid' => 'string',

            // users
            'users' => 'array',

            // users to delete
            'users_to_delete' => 'array',

            // files
            'files' => 'array',

            // files to delete
            'files_to_delete' => 'array'
        ]);

        #endregion

        #region Check exsist models

        $result_check = [];
        // Check Email
        /*$result_check['task'] = Email::where('email', $value['email'])
                                        ->where('hosting_uuid', $value['hosting_uuid'])
                                        ->orWhere('phone', $value['phone'])
                                        ->first();*/

        $exsist = false;
        foreach ($result_check AS $key => $value):
            if ($value != null){
                $exsist = true;
                break;
            }
        endforeach;

        if ($exsist){
            return response()->json([
                        'data' => $result_check,
                    ], 409);
        }

        #endregion

        $task->update($validated);

        #region Users add (if exsist)

        if (isset($validated['department_uuid'])){
            $task_to_user = TaskToUser::where('status', 1)
                                        ->where('task_uuid', $task['uuid'])
                                        ->where('department_uuid')->count();

            //TODO: Have to check it again
            if (isset($validated['users'])){
                if (!$task_to_user){ // if department not found
                    TaskToUser::where('task_uuid', $task['uuid'])->update(['status' => 0]);
                }
                foreach($validated['users'] AS $key => $value):
                    $task_to_user = new TaskToUser();
                    $task_to_user->user_uuid = $value;
                    $task_to_user->task_uuid = $task['uuid'];
                    $task_to_user->department_uuid = $validated['department_uuid'];
                    $task_to_user->group = 0;
                    $task_to_user->save();
                endforeach;
            }else{
                $tmp_group = 0;
                if ($task_to_user && $task_to_user==count($validated['users_to_delete'])){ // if department found && all users delete
                    $tmp_group = 1;
                }

                $task_to_user = new TaskToUser();
                $task_to_user->task_uuid = $task['uuid'];
                $task_to_user->user_uuid = $validated['user_uuid'];
                $task_to_user->department_uuid = $validated['department_uuid'];
                $task_to_user->group = $tmp_group;
                $task_to_user->save();
            }
        }

        #endregion

        #region Users to delete (if exsist)

        if (isset($validated['users_to_delete'])){
            foreach($validated['users_to_delete'] AS $key => $value):
                $task_to_user = TaskToUser::where('task_uuid', $task['uuid'])->where('user_uuid', $value);
                $task_to_user->update(['status' => 0]);
            endforeach;
        }

        #endregion

        #region Files add (if exsist)

        if ($request->has('files')){
            $files = $request->file('files');
            foreach ($files AS $key => $value):
                $tmp_file = $value;
                $file_parent = $key;

                foreach ($tmp_file AS $key2 => $value2):
                    $file = new File();
                    $file->user_uuid = $validated['user_uuid'];
                    $file->entity_uuid = $task['uuid'];
                    $file->file_name = Str::uuid()->toString() . '.' . $value2->getClientOriginalExtension();
                    $file->file_path = $file->file_name;
                    $file->file_parent = $file_parent;
                    $value2->move('uploads', $file->file_path);
                    $file->save();
                endforeach;
            endforeach;
        }

        #endregion

        #region Files to delete (if exsist)

        if (isset($validated['files_to_delete'])){
            foreach($validated['files_to_delete'] AS $key => $value):
                $file = File::find($value);
                $file->update(['status' => 0]);
            endforeach;
        }

        #endregion

        return new TaskResource($task);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Task $task)
    {
        $task->update(['status' => Config::get('common.status.actived')]);
    }
}
