<?php

namespace App\Http\Controllers;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\UserAccessTokenResource;
use App\Http\Resources\UserResource;
use App\Models\API\Activity;
use App\Models\API\User;
use App\Models\API\UserAccessToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class UserController extends Controller
{
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
    public function index()
    {
        $user = User::where('status', Config::get('common.status.actived'))
                        ->paginate(100);
        return UserResource::collection($user);
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
        #region Validation

        $validated = $request->validate([
            'department_uuid' => 'required|string',
            'role_uuid' => 'required|string',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:200',
            'telegram' => 'required|string|max:100',
        ]);

        #endregion

        #region Check if exsist data

        $check = [];

        if (isset($validated['username'])){
            // username
            $check['user'] = User::select('username')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('username', $validated['username'])
                                    ->first();
            if ($check['user']!=null){
                $check['user'] = $check['user']->toArray();
                foreach ($check['user'] as $key => $value):
                    $check[$key] = '~Exsist';
                endforeach;
            }
            unset($check['user']);

            // telegram
            $check['contact'] = User::select('telegram')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('telegram', $validated['telegram'])
                                    ->first();
            if ($check['contact']!=null){
                $check['contact'] = $check['contact']->toArray();
                foreach ($check['contact'] as $key => $value):
                    $check[$key] = '~Exsist';
                endforeach;
            }
            unset($check['contact']);
        }

        if (count($check)>0){
            return response()->json([
                        'data' => $check,
                    ], 409);
        }

        #endregion

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
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(User $user)
    {
        return new UserResource($user);
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
        #region Validation

        $validated = $request->validate([
            'department_uuid' => 'string',
            'role_uuid' => 'string',
            'first_name' => 'string|max:100',
            'last_name' => 'string|max:100',
            'username' => 'string|max:100',
            'password' => 'string|max:200',
            'telegram' => 'string|max:100'
        ]);

        #endregion

        #region Check if exsist data

        $check = [];

        #region Check Username

        if (isset($validated['username'])){
            // username
            $check['user'] = User::select('username')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('uuid', '!=', $user['uuid'])
                                    ->where('username', $validated['username'])
                                    ->first();
            if ($check['user']!=null){
                $check['user'] = $check['user']->toArray();
                foreach ($check['user'] as $key => $value):
                    $check[$key] = '~Exsist';
                endforeach;
            }
            unset($check['user']);
        }

        #endregion

        #region Check telegram

        if (isset($validated['telegram'])){
            // telegram
            $check['contact'] = User::select('telegram')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('uuid', '!=', $user['uuid'])
                                    ->where('telegram', $validated['telegram'])
                                    ->first();
            if ($check['contact']!=null){
                $check['contact'] = $check['contact']->toArray();
                foreach ($check['contact'] as $key => $value):
                    $check[$key] = '~Exsist';
                endforeach;
            }
            unset($check['contact']);
        }

        if (count($check)>0){
            return response()->json([
                        'data' => $check,
                    ], 409);
        }

        #endregion

        #endregion

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
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(User $user)
    {
        $user->update(['status' => Config::get('common.status.deleted')]);
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
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:100',
        ]);

        $user = User::where('username', $validated['username'])
                        ->where('password', $validated['password'])
                        ->where('status', Config::get('common.status.actived'))
                        ->first();

        if (!$user){
            return response()->json([
                'data' => ['msg' => 'Invalid username or password'],
            ], 404);
        }

        $token = Str::random(32);
        $expires_at = Carbon::now();
        $expires_at = $expires_at->addDays(7)->toDateTimeString(); // after 7 day expires

        $user['access_token'] = ['user_uuid' => $user['uuid'], 'token' => $token, 'expires_at' => $expires_at];

        UserAccessToken::create($user['access_token']);

        // Activity log
        Activity::create([
            'user_uuid' => $user['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' =>  UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.logged'),
            'status' => Config::get('common.status.actived')
        ]);

        return $user;
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

        #region Validation

        $validated = $request->validate([
            'token' => 'required|string'
        ]);

        #endregion

        UserAccessToken::where('token', $validated['token'])->update(['status' => Config::get('common.status.deleted')]);

        return response()->json([
            'data' => ['msg' => 'Logged out'],
        ], 200);
    }
}
