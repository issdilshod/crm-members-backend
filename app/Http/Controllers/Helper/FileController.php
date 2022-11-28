<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Services\Helper\FileService;
use Illuminate\Http\Request;

class FileController extends Controller
{

    private $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    /**     @OA\POST(
      *         path="/api/file-upload",
      *         operationId="file_upload",
      *         tags={"Helper"},
      *         summary="Upload file",
      *         description="Upload file",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"first_name", "files"},
      *                         @OA\Property(property="file_parent", type="text"),
      *
      *                         @OA\Property(property="files[]", type="file", format="binary")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file_parent' => 'required',
            'user_uuid' => ''
        ]);

        if ($request->has('files')){
            $respond = $this->fileService->record($request->file('files'), $validated);
        }else{
            $respond = response()->json([
                'msg' => 'File not exists.'
            ], 422);
        }

        return $respond;
    }

}