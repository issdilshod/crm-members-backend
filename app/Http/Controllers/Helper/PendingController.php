<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Services\Company\CompanyService;
use App\Services\Director\DirectorService;
use Illuminate\Http\Request;

class PendingController extends Controller
{

    private $directorService;
    private $companyService;

    public function __construct()
    {
        $this->directorService = new DirectorService();
        $this->companyService = new CompanyService();
    }
    
    /**     @OA\GET(
      *         path="/api/pending",
      *         operationId="list_pending_by_user",
      *         tags={"Helper"},
      *         summary="List of pending by user",
      *         description="List of pending by user",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Authorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function by_user(Request $request)
    {
        $directors = $this->directorService->by_user($request->user_uuid);
        $companies = $this->companyService->by_user($request->user_uuid);
        return ['directors' => $directors, 'companies' => $companies];
    }

}
