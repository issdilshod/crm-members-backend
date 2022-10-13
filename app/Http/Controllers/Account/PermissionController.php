<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\PermissionResource;
use App\Http\Resources\Account\RolePermissionResource;
use App\Http\Resources\Account\UserPermissionResource;
use App\Models\Account\Permission;
use App\Models\Account\RolePermission;
use App\Models\Account\UserPermission;
use App\Models\Helper\Department;
use App\Models\Helper\Role;
use App\Policies\PermissionPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PermissionController extends Controller
{

    /**     @OA\GET(
      *         path="/api/permission",
      *         operationId="permission",
      *         tags={"Account"},
      *         summary="Get list of permission",
      *         description="Get list of permisiion",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $permissions = Permission::where('status', Config::get('common.status.actived'))->get();
        return PermissionResource::collection($permissions);
    }

    /**     @OA\GET(
      *         path="/api/permission-department/{uuid}",
      *         operationId="permission_department",
      *         tags={"Account"},
      *         summary="Get department permissions",
      *         description="Get department permissions",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="department uuid",
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
    public function by_department(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $department = Department::where('status', Config::get('common.status.actived'))
                                    ->where('uuid', $uuid)
                                    ->first();

        $role = Role::where('status', Config::get('common.status.actived'))
                        ->where('alias', $department->alias)
                        ->first();

        $permissions = RolePermission::where('status', Config::get('common.status.actived'))
                                        ->where('role_uuid', $role->uuid)
                                        ->get();
                                    
        return RolePermissionResource::collection($permissions);
    }

    /**     @OA\GET(
      *         path="/api/permission-user/{uuid}",
      *         operationId="permission_user",
      *         tags={"Account"},
      *         summary="Get user permissions",
      *         description="Get user permissions",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="user uuid",
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
    public function by_user(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $permissions = UserPermission::where('status', Config::get('common.status.actived'))
                                        ->where('user_uuid', $uuid)
                                        ->get();
                                    
        return UserPermissionResource::collection($permissions);
    }

    /**     @OA\POST(
      *         path="/api/permission-department",
      *         operationId="alter_department_permission",
      *         tags={"Account"},
      *         summary="Alter department permission",
      *         description="Alter department permission",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"permission_uuid", "department_uuid", "status"},
      *                         @OA\Property(property="permission_uuid", type="text"),
      *                         @OA\Property(property="department_uuid", type="text"),
      *                         @OA\Property(property="status", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function department(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'department_uuid' => 'required',
            'permission_uuid' => 'required',
            'status' => 'required'
        ]);

        $department = Department::where('status', Config::get('common.status.actived'))
                                    ->where('uuid', $validated['department_uuid'])
                                    ->first();

        $role = Role::where('status', Config::get('common.status.actived'))
                        ->where('alias', $department->alias)
                        ->first();

        $validated['role_uuid'] = $role->uuid;

        $permission = RolePermission::where('role_uuid', $validated['role_uuid'])
                                        ->where('permission_uuid', $validated['permission_uuid'])
                                        ->first();
        if ($permission==null){
            RolePermission::create($validated);
        }else{
            if ($validated['status']){
                $permission->update(['status' => Config::get('common.status.actived')]);
            }else{
                $permission->update(['status' => Config::get('common.status.deleted')]); 
            }
        }
    }

    /**     @OA\POST(
      *         path="/api/permission-user",
      *         operationId="alter_user_permission",
      *         tags={"Account"},
      *         summary="Alter user permission",
      *         description="Alter user permission",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"permission_uuid", "user_uuid", "status"},
      *                         @OA\Property(property="user_uuid", type="text"),
      *                         @OA\Property(property="department_uuid", type="text"),
      *                         @OA\Property(property="status", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function user(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'sel_user_uuid' => 'required',
            'permission_uuid' => 'required',
            'status' => 'required'
        ]);
        // change doing user to selected
        $validated['user_uuid'] = $validated['sel_user_uuid'];

        $permission = UserPermission::where('user_uuid', $validated['user_uuid'])
                                        ->where('permission_uuid', $validated['permission_uuid'])
                                        ->first();
        if ($permission==null){
            UserPermission::create($validated);
        }else{
            if ($validated['status']){
                $permission->update(['status' => Config::get('common.status.actived')]);
            }else{
                $permission->update(['status' => Config::get('common.status.deleted')]); 
            }
        }
    }
}
