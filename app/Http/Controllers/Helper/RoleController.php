<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\RoleResource;
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
    public function index(RoleService $roleService)
    {
        $roles = $roleService->getRoles();
        
        return RoleResource::collection($roles);
    }

    public function store(Request $request)
    {
        /*$validated = $request->validate([
            'role_name' => 'required|string|max:100'
        ]);
        return new RoleResource(Role::create($validated));*/
    }
}
