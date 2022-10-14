<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\NoteResource;
use App\Models\Helper\Note;
use App\Policies\PermissionPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NoteController extends Controller
{

    /**     @OA\POST(
      *         path="/api/note",
      *         operationId="post_note",
      *         tags={"Helper"},
      *         summary="Post note",
      *         description="Post note",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"text"},
      *                         @OA\Property(property="text", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'user_uuid' => 'string'
        ]);

        $note = Note::create($validated);

        return new NoteResource($note);
    }

    /**     @OA\GET(
      *         path="/api/note/{uuid}",
      *         operationId="get_note",
      *         tags={"Helper"},
      *         summary="Get note",
      *         description="Get note",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="note uuid",
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
    public function show(Request $request, Note $note)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            if ($note->user_uuid!=$request->user_uuid){ // note not belong to user
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        return new NoteResource($note);
    }

    /**     @OA\GET(
      *         path="/api/note_by_user",
      *         operationId="get_note_by_user",
      *         tags={"Helper"},
      *         summary="Get note by user",
      *         description="Get note by user",
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show_by_user(Request $request)
    {
        $validated = $request->validate([
            'user_uuid' => 'string'
        ]);
        $note = Note::where('status', Config::get('common.status.actived'))
                        ->where('user_uuid', $validated['user_uuid'])
                        ->first();
        if ($note==null){
            return response()->json([
                'data' => ['msg' => 'User does\'t have a note.']
            ], 404);
        }
        return new NoteResource($note);
    }

    /**     @OA\PUT(
      *         path="/api/note/{uuid}",
      *         operationId="update_note",
      *         tags={"Helper"},
      *         summary="Update note",
      *         description="Update note",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="note uuid",
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
      *                         required={"text"},
      *                         @OA\Property(property="text", type="text")
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
    public function update(Request $request, Note $note)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            if ($note['user_uuid']!=$request->user_uuid){
                return response()->json([
                    'data' => ['msg' => 'It\'s not your note, this why you can\'t update it!']
                ], 403);
            }
        }

        $validated = $request->validate([
            'text' => 'required',
            'user_uuid' => 'string'
        ]);

        $note->update($validated);

        return new NoteResource($note);
    }

}
