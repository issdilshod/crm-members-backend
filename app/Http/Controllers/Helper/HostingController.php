<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helper\HostingResource;
use App\Services\Helper\HostingService;

class HostingController extends Controller
{

    private $hostingService;

    public function __construct()
    {
        $this->hostingService = new HostingService();
    }

    /**     @OA\GET(
      *         path="/api/hosting",
      *         operationId="list_hosting",
      *         tags={"Helper"},
      *         summary="List of hosting",
      *         description="List of hosting",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index()
    {
        $hostings = $this->hostingService->getHostings();
        return HostingResource::collection($hostings);
    }

}
