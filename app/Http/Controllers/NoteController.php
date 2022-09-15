<?php

namespace App\Http\Controllers;

use App\Http\Resources\NoteResource;
use App\Models\API\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NoteController extends Controller
{

    /**     @OA\POST(
      *         path="/api/note",
      *         operationId="post_note",
      *         tags={"Note"},
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
      *         tags={"Note"},
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
    public function show(Note $note)
    {
        return new NoteResource($note);
    }

    /**     @OA\GET(
      *         path="/api/note_by_user",
      *         operationId="get_note_by_user",
      *         tags={"Note"},
      *         summary="Get note by user",
      *         description="Get note by user",
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
      *         path="/api/note",
      *         operationId="note_task",
      *         tags={"Note"},
      *         summary="Note task",
      *         description="Note task",
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=403, description="Permission"),
      *             @OA\Response(response=404, description="Resource Not Found")
      *     )
      */
    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'user_uuid' => 'string'
        ]);

        // permission (can change his own note)
        if ($validated['user_uuid']!=$note['user_uuid']){
            return response()->json([
                'data' => ['msg' => 'It\'s not your note, this why you can\'t update it!']
            ], 403);
        }

        $note->update($validated);

        return new NoteResource($note);
    }

}
