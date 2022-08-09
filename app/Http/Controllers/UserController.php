<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\UserResource;
use App\Models\API\Activity;
use App\Models\API\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**     @OA\Get(
      *         path="/api/user",
      *         operationId="list_user",
      *         tags={"Account"},
      *         summary="List of user",
      *         description="List of user",
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
        $user = User::where('status', '=', '1')->with('activities')->get();
        return UserResource::collection($user);
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

    /**     @OA\POST(
      *         path="/api/user",
      *         operationId="post_user",
      *         tags={"Account"},
      *         summary="Add user",
      *         description="Add user",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"first_name", "last_name", "username", "password", "telegram", "status"},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="username", type="text"),
      *                         @OA\Property(property="password", type="text"),
      *                         @OA\Property(property="telegram", type="text"),
      *                         @OA\Property(property="status", type="int"),
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:200',
            'telegram' => 'required|string|max:100',
            'status' => 'required|integer'
        ]);
        return new UserResource(User::create($validated));
    }

    /**     @OA\GET(
      *         path="/api/user/{uuid}",
      *         operationId="get_user",
      *         tags={"Account"},
      *         summary="Get user",
      *         description="Get user",
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(User $user)
    {
        //
        $user = User::with('activities')->get()->find($user);
        return new UserResource($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\API\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**     @OA\PUT(
      *         path="/api/user/{uuid}",
      *         operationId="update_user",
      *         tags={"Account"},
      *         summary="Update user",
      *         description="Update user",
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
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"first_name", "last_name", "username", "password", "telegram", "status"},
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="username", type="text"),
      *                         @OA\Property(property="password", type="text"),
      *                         @OA\Property(property="telegram", type="text"),
      *                         @OA\Property(property="status", type="int")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function update(Request $request, User $user)
    {
        //
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:200',
            'telegram' => 'required|string|max:100',
            'status' => 'required|integer'
        ]);
        $user->update($validated);
        return new UserResource($user);
    }

    /**     @OA\DELETE(
      *         path="/api/user/{uuid}",
      *         operationId="delete_user",
      *         tags={"Account"},
      *         summary="Delete user",
      *         description="Delete user",
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(User $user)
    {
        //
        $user->update(['status' => '0']);
    }
}
