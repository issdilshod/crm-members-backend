<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\HostingResource;
use App\Services\Helper\HostingService;
use Illuminate\Http\Request;

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
    public function index(HostingService $hostingService)
    {
        $hostings = $hostingService->getHostings();
        return HostingResource::collection($hostings);
    }

    public function store(Request $request)
    {
        /*$validated = $request->validate([
            'host' => 'required|string|max:100'
        ]);
        return new HostingResource(Hosting::create($validated));*/
    }

}
