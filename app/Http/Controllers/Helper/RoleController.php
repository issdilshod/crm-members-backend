<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\RoleResource;
use App\Policies\PermissionPolicy;
use App\Services\Helper\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**     @OA\GET(
      *         path="/api/role",
      *         operationId="list_role",
      *         tags={"Helper"},
      *         summary="List of role",
      *         description="List of role",
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
    public function index(Request $request, RoleService $roleService)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $roles = $roleService->getRoles();
        return RoleResource::collection($roles);
    }
}
