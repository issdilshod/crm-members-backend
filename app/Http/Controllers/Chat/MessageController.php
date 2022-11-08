<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Message;
use App\Services\Chat\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{

    private $messageService;

    public function __construct()
    {
        $this->messageService = new MessageService();
    }

    /**     @OA\GET(
      *         path="/api/chat-messages/{chat_uuid}",
      *         operationId="get_chat_messages",
      *         tags={"Chat"},
      *         summary="Get chat messages",
      *         description="Get chat messages",
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
    public function by_chat(Request $request, $chat_uuid)
    {
        // check permission

        $messages = $this->messageService->chat_messages($chat_uuid);

        return $messages;
    }
    
    /**     @OA\POST(
      *         path="/api/message",
      *         operationId="post_message",
      *         tags={"Chat"},
      *         summary="Add message",
      *         description="Add message",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"chat_uuid"},
      *                         
      *                         @OA\Property(property="chat_uuid", type="text"),
      *                         @OA\Property(property="message", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_uuid' => 'required',
            'user_uuid' => 'required',
            'message' => ''
        ]);

        // check permission

        $message = $this->messageService->create($validated);
        return $message;
    }

    /**     @OA\GET(
      *         path="/api/message/{uuid}",
      *         operationId="get_message",
      *         tags={"Chat"},
      *         summary="Get message",
      *         description="Get message",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="message uuid",
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
    public function show(Message $message)
    {
        $message = $this->messageService->one($message);
        return $message;
    }

    /**     @OA\PUT(
      *         path="/api/message/{uuid}",
      *         operationId="update_message",
      *         tags={"Chat"},
      *         summary="Update message",
      *         description="Update message",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="message uuid",
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
      *
      *                         @OA\Property(property="message", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function update(Request $request, Message $message)
    {
        $validated = $request->validate([
            'message' => ''
        ]);

        // check permission

        $message = $this->messageService->update($message, $validated);
        return $message;
    }
 
    /**     @OA\DELETE(
      *         path="/api/message/{uuid}",
      *         operationId="delete_message",
      *         tags={"Chat"},
      *         summary="Delete message",
      *         description="Delete message",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="message uuid",
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
    public function destroy(Message $message)
    {
        // check permission

        $this->messageService->delete($message);
    }
}
