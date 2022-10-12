<?php

namespace App\Http\Controllers\Account;

use App\Helpers\TelegramHelper;
use App\Helpers\UserSystemInfoHelper;
use App\Helpers\WebSocket;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\UserResource;
use App\Models\Account\Activity;
use App\Models\Account\InviteUser;
use App\Models\Account\User;
use App\Models\Account\UserAccessToken;
use App\Notifications\TelegramNotification;
use App\Policies\PermissionPolicy;
use App\Services\Account\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class UserController extends Controller
{

    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**     @OA\GET(
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
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }
        
        $users = $this->userService->all();
        return $users;
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
      *                         required={"department_uuid", "role_uuid", "first_name", "last_name", "username", "password", "telegram"},
      *                         @OA\Property(property="department_uuid", type="text"),
      *                         @OA\Property(property="role_uuid", type="text"),
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="username", type="text"),
      *                         @OA\Property(property="password", type="text"),
      *                         @OA\Property(property="telegram", type="text")
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
      *     )
      */
    public function store(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }
        
        $validated = $request->validate([
            'department_uuid' => 'required|string',
            'role_uuid' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'telegram' => 'required|string',
            'user_uuid' => 'string'
        ]);

        $check = [];

        $check = $this->userService->check($validated);
        
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $user = $this->userService->create($validated);

        return new UserResource($user);
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
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Request $request, User $user)
    {
        $user = $this->userService->one($user);
        return $user;
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
      *                         required={},
      *                         @OA\Property(property="department_uuid", type="text"),
      *                         @OA\Property(property="role_uuid", type="text"),
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="username", type="text"),
      *                         @OA\Property(property="password", type="text"),
      *                         @OA\Property(property="telegram", type="text")
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
      *     )
      */
    public function update(Request $request, User $user)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }
        
        $validated = $request->validate([
            'department_uuid' => 'required',
            'role_uuid' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required',
            'password' => 'required',
            'telegram' => 'required',
            'user_uuid' => 'string'
        ]);

        $check = [];

        $check = $this->userService->check_ignore($validated, $user->uuid);

        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $this->userService->update($user, $validated);

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
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, User $user)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $this->userService->delete($user);
    }

    /**     @OA\PUT(
      *         path="/api/user/accept/{uuid}",
      *         operationId="accept_user",
      *         tags={"Account"},
      *         summary="Accept user",
      *         description="Acccept user",
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
      *                         required={},
      *                         @OA\Property(property="department_uuid", type="text"),
      *                         @OA\Property(property="role_uuid", type="text"),
      *                         @OA\Property(property="first_name", type="text"),
      *                         @OA\Property(property="last_name", type="text"),
      *                         @OA\Property(property="username", type="text"),
      *                         @OA\Property(property="password", type="text"),
      *                         @OA\Property(property="telegram", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function accept(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'department_uuid' => 'required',
            'role_uuid' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'username' => 'required',
            'password' => 'required',
            'telegram' => 'required',
            'user_uuid' => 'string'
        ]);

        $user = User::where('uuid', $uuid)->first();

        $check = [];

        $check = $this->userService->check_ignore($validated, $user->uuid);

        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $this->userService->accept($user, $validated);

        return new UserResource($user);
    }

    /**     @OA\PUT(
      *         path="/api/user/reject/{uuid}",
      *         operationId="reject_user",
      *         tags={"Account"},
      *         summary="Reject user",
      *         description="Reject user",
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
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function reject(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $this->userService->reject($uuid, $request->user_uuid);
    }

    /**     @OA\POST(
      *         path="/api/login",
      *         operationId="login",
      *         tags={"Account"},
      *         summary="Login",
      *         description="Login",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"username", "password"},
      *                         @OA\Property(property="username", type="text"),
      *                         @OA\Property(property="password", type="password")
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
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $respond = $this->userService->login($validated);

        return $respond;
    }

    /**     @OA\GET(
      *         path="/api/is_auth",
      *         operationId="is_auth",
      *         tags={"Account"},
      *         summary="Is Auth",
      *         description="Is Auth",
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function is_auth()
    {
        // NOTE: Check in MiddleWare (AuthentificateCustom)
        return response()->json([
            'data' => ['msg' => 'Authentificate'],
        ], 200);
    }

    /**     @OA\POST(
      *         path="/api/logout",
      *         operationId="logout",
      *         tags={"Account"},
      *         summary="Logout",
      *         description="Logout",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"token"},
      *                         @OA\Property(property="token", type="text")
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
    public function logout(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'user_uuid' => 'string'
        ]);

        $this->userService->logout($validated);

        return response()->json([
            'data' => ['msg' => 'Logged out'],
        ], 200);
    }

    /**     @OA\POST(
      *         path="/api/register",
      *         operationId="register",
      *         tags={"Account"},
      *         summary="Register user",
      *         description="Register user",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"entry_token", "username", "password"},
      *                         @OA\Property(property="entry_token", type="text"),
      *                         @OA\Property(property="username", type="text"),
      *                         @OA\Property(property="password", type="text")
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
    public function register(Request $request)
    {
        $validated = $request->validate([
            'entry_token' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'telegram' => 'required',
            'username' => 'required',
            'password' => 'required'
        ]);

        $response = $this->userService->register($validated);

        return $response;
    }

    /**     @OA\GET(
      *         path="/api/pending-users",
      *         operationId="get_pending_users",
      *         tags={"Account"},
      *         summary="Get pending users",
      *         description="Get pending users",
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
    public function pending_users()
    {
        $users = $this->userService->pendings();
        return $users;
    }

    /**     @OA\GET(
      *         path="/api/get_me",
      *         operationId="get_me",
      *         tags={"Account"},
      *         summary="Get Me",
      *         description="Get Me",
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
    public function get_me(Request $request)
    {
        $user = $this->userService->me($request);
        return $user;
    }
}
