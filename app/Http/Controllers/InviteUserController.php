<?php

namespace App\Http\Controllers;

use App\Helpers\UserSystemInfoHelper;
use App\Mail\EmailInvite;
use App\Models\API\Activity;
use App\Models\API\InviteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteUserController extends Controller
{

  /**     @OA\POST(
    *         path="/api/invite-via-email",
    *         operationId="post_invite_via_email",
    *         tags={"Invite"},
    *         summary="Invite User via Email",
    *         description="Invite User",
    *             @OA\RequestBody(
    *                 @OA\JsonContent(),
    *                 @OA\MediaType(
    *                     mediaType="multipart/form-data",
    *                     @OA\Schema(
    *                         type="object",
    *                         required={"unique_identify"},
    *                         @OA\Property(property="unique_identify", type="text")
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
    *             @OA\Response(response=404, description="Resource Not Found")
    *     )
    */
  public function via_email(Request $request)
  {
      $validated = $request->validate([
          'unique_identify' => 'required',
          'user_uuid' => 'string'
      ]);

      $validated['entry_token'] = Str::random(32);
      $validated['via'] = Config::get('common.invite.email');

      $invite_user = InviteUser::create($validated);

      // Send mail
      $link = env('APP_FRONTEND_ENDPOINT') . '/register/'. $validated['entry_token'];
      Mail::to($validated['unique_identify'])
              ->send(new EmailInvite($link));

      // Activity log
      Activity::create([
        'user_uuid' => $validated['user_uuid'],
        'entity_uuid' => $invite_user['uuid'],
        'device' => UserSystemInfoHelper::device_full(),
        'ip' => UserSystemInfoHelper::ip(),
        'description' => Config::get('common.activity.user.invite_via_email'),
        'changes' => json_encode($validated),
        'action_code' => Config::get('common.activity.codes.user_invite_via_email'),
        'status' => Config::get('common.status.actived')
      ]);

      return response()->json([
                'data' => 'Success',
            ], 200);
  }

  /**     @OA\POST(
    *         path="/api/invite-check-token",
    *         operationId="post_invite_check_token",
    *         tags={"Invite"},
    *         summary="Invite Check Token",
    *         description="Invite Check",
    *             @OA\RequestBody(
    *                 @OA\JsonContent(),
    *                 @OA\MediaType(
    *                     mediaType="multipart/form-data",
    *                     @OA\Schema(
    *                         type="object",
    *                         required={"entry_token"},
    *                         @OA\Property(property="entry_token", type="text")
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
    *             @OA\Response(response=404, description="Resource Not Found")
    *     )
    */
  public function check_token(Request $request)
  {

      $validated = $request->validate([
        'entry_token' => 'required'
      ]);

      $invited_user = InviteUser::select('unique_identify', 'via', 'entry_token')
                                  ->where('status', Config::get('common.status.actived'))
                                  ->where('entry_token', $validated['entry_token'])
                                  ->first();
      
      if ($invited_user!=null){
        return response()->json([
                  'data' => $invited_user->toArray(),
              ], 200);
      }

      return response()->json([
                'data' => 'Invalid token!',
            ], 404);
  }

}
