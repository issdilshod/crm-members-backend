<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Models\API\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class RoleController extends Controller
{
    /**     @OA\GET(
      *         path="/api/role",
      *         operationId="list_role",
      *         tags={"Account"},
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
    public function index()
    {
        return RoleResource::collection(Role::all()->where('status', Config::get('common.status.actived')));
    }

    public function store(Request $request)
    {
        /*$validated = $request->validate([
            'role_name' => 'required|string|max:100'
        ]);
        return new RoleResource(Role::create($validated));*/
    }
}
