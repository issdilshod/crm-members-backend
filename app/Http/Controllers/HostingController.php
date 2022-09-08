<?php

namespace App\Http\Controllers;

use App\Http\Resources\HostingResource;
use App\Models\API\Hosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class HostingController extends Controller
{
    /**     @OA\GET(
      *         path="/api/hosting",
      *         operationId="list_hosting",
      *         tags={"Helper"},
      *         summary="List of hosting",
      *         description="List of hosting",
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
        return HostingResource::collection(Hosting::all()->where('status', Config::get('common.status.actived')));
    }

    public function store(Request $request)
    {
        /*$validated = $request->validate([
            'host' => 'required|string|max:100'
        ]);
        return new HostingResource(Hosting::create($validated));*/
    }

}
