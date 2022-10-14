<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\StateResource;
use App\Models\Helper\State;
use Illuminate\Support\Facades\Config;

class StateController extends Controller
{
    /**     @OA\GET(
      *         path="/api/state",
      *         operationId="list_state",
      *         tags={"Helper"},
      *         summary="List of state",
      *         description="List of state",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated")
      *     )
      */
    public function index()
    {
        $states = State::orderBy('full_name')
                            ->where('status', Config::get('common.status.actived'))
                            ->get();
        return StateResource::collection($states);
    }

}
