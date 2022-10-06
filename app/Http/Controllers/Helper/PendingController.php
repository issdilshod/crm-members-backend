<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Services\Helper\PendingService;
use Illuminate\Http\Request;

class PendingController extends Controller
{

    private $pendingService;

    public function __construct()
    {
        $this->pendingService = new PendingService();
    }
    
    /**     @OA\GET(
      *         path="/api/pending",
      *         operationId="list_pending",
      *         tags={"Helper"},
      *         summary="List of pending",
      *         description="List of pending",
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
        //
    }
}
