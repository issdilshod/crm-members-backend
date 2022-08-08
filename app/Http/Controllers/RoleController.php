<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Models\API\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**     @OA\Get(
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
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index()
    {
        //
        return RoleResource::collection(Role::all()->where('status', '=', '1'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'role_name' => 'required|string|max:100',
            'status' => 'required|integer'
        ]);
        return new RoleResource(Role::create($validated));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\API\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\API\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\API\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\API\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        //
    }
}
