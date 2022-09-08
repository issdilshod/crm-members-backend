<?php

namespace App\Http\Controllers;

use App\Http\Resources\StateResource;
use App\Models\API\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class StateController extends Controller
{
    /**     @OA\GET(
      *         path="/api/state",
      *         operationId="list_state",
      *         tags={"Helper"},
      *         summary="List of state",
      *         description="List of state",
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
        return StateResource::collection(State::orderBy('full_name')->where('status', Config::get('common.status.actived'))->get());
    }

    public function store(Request $request)
    {
        /*$validated = $request->validate([
            'short_name' => 'required|string|max:20',
            'full_name' => 'required|string|max:50'
        ]);
        return new StateResource(State::create($validated));*/
    }
}
