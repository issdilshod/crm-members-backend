<?php

namespace App\Http\Controllers;

use App\Mail\EmailInvite;
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
          'unique_identify' => 'required'
      ]);

      $validated['entry_token'] = Str::random(32);
      $validated['via'] = Config::get('common.invite.email');

      InviteUser::create($validated);

      // Send mail
      $link = '?token='.$validated['entry_token'];
      Mail::to($validated['unique_identify'])
              ->send(new EmailInvite($link));

      return response()->json([
                'data' => 'Success',
            ], 200);
  }

}
