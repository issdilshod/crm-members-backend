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
}
