<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Chat;
use App\Policies\PermissionPolicy;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ChatController extends Controller
{

    private $chatService;

    public function __construct()
    {
        $this->chatService = new ChatService();
    }
    
    /**     @OA\GET(
      *         path="/api/chat",
      *         operationId="list_chat",
      *         tags={"Chat"},
      *         summary="List of chat",
      *         description="List of chat",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index()
    {
        // permission

        $chats = $this->chatService->all();

        return $chats;
    }

    /**     @OA\POST(
      *         path="/api/chat",
      *         operationId="post_chat",
      *         tags={"Chat"},
      *         summary="Add chat",
      *         description="Add chat",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"name"},
      *                         
      *                         @OA\Property(property="name", type="text"),
      *                         @OA\Property(property="members[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function store(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.chat.store'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'name' => 'required',
            'members' => 'array',
            'user_uuid' => ''
        ]);

        $chat = $this->chatService->create($validated);
        return $chat;
    }

    /**     @OA\GET(
      *         path="/api/chat/{uuid}",
      *         operationId="get_chat",
      *         tags={"Chat"},
      *         summary="Get chat",
      *         description="Get chat",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="chat uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Chat $chat)
    {
        // permission

        $chat = $this->chatService->one($chat);
        return $chat;
    }

    /**     @OA\PUT(
      *         path="/api/chat/{uuid}",
      *         operationId="update_chat",
      *         tags={"Chat"},
      *         summary="Update chat",
      *         description="Update chat",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="chat uuid",
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
      *                         required={"name"},
      *
      *                         @OA\Property(property="name", type="text"),
      *                         @OA\Property(property="members[]", type="text"),
      *                         @OA\Property(property="members_to_delete[]", type="text"),
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function update(Request $request, Chat $chat)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.chat.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            } else {
                if ($chat->user_uuid!=$request->user_uuid){ // chat not created by user
                    return response()->json([ 'data' => 'Not Authorized' ], 403);
                }
            }
        }

        $validated = $request->validate([
            'name' => 'required',
            'members' => 'array',
            'members_to_delete' => 'array',
        ]);

        $chat = $this->chatService->update($chat, $validated, $request->user_uuid);
        return $chat;
    }

    /**     @OA\DELETE(
      *         path="/api/chat/{uuid}",
      *         operationId="delete_chat",
      *         tags={"Chat"},
      *         summary="Delete chat",
      *         description="Delete chat",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="chat uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Request $request, Chat $chat)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.chat.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            } else {
                if ($chat->user_uuid!=$request->user_uuid){ // chat not created by user
                    return response()->json([ 'data' => 'Not Authorized' ], 403);
                }
            }
        }

        $this->chatService->delete($chat);
    }
}
