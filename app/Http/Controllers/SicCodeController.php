<?php

namespace App\Http\Controllers;

use App\Http\Resources\SicCodeResource;
use App\Models\API\SicCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SicCodeController extends Controller
{
    /**     @OA\GET(
      *         path="/api/sic_code",
      *         operationId="list_sic_code",
      *         tags={"Helper"},
      *         summary="List of sic code",
      *         description="List of sic code",
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
        $sic_codes = SicCode::orderBy('code', 'ASC')
                                ->where('status', Config::get('common.status.actived'))
                                ->get();
        return SicCodeResource::collection($sic_codes);
    }
    
}
