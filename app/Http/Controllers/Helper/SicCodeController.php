<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\SicCodeResource;
use App\Models\Helper\SicCode;
use Illuminate\Support\Facades\Config;

class SicCodeController extends Controller
{
    /**     @OA\GET(
      *         path="/api/sic_code",
      *         operationId="list_sic_code",
      *         tags={"Helper"},
      *         summary="List of sic code",
      *         description="List of sic code",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated")
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
