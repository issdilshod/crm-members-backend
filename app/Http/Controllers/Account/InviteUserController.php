<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\Account\InviteUserService;
use Illuminate\Http\Request;

class InviteUserController extends Controller
{

    private $inviteUserService;

    public function __construct()
    {
        $this->inviteUserService = new InviteUserService();
    }

    /**     @OA\POST(
        *         path="/api/invite-via-email",
        *         operationId="post_invite_via_email",
        *         tags={"Account"},
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

        $response = $this->inviteUserService->via_email($validated);

        return $response;
    }

    /**     @OA\POST(
        *         path="/api/invite-via-telegram",
        *         operationId="post_invite_via_telegram",
        *         tags={"Account"},
        *         summary="Invite User via Telegram",
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
    public function via_telegram(Request $request)
    {
        $validated = $request->validate([
            'unique_identify' => 'required',
            'user_uuid' => 'string'
        ]);

        $response = $this->inviteUserService->via_telegram($validated);

        return $response;
    }

    /**     @OA\POST(
        *         path="/api/invite-check-token",
        *         operationId="post_invite_check_token",
        *         tags={"Account"},
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

        $response = $this->inviteUserService->check_token($validated);
        
        return $response;
    }

}
