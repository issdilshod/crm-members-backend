<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Policies\PermissionPolicy;
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
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function by_user(Request $request)
    {
        // if now headquarters then show only belongs to them
        if (!PermissionPolicy::permission($request->user_uuid)){
            $directors = $this->directorService->by_user($request->user_uuid);
            $companies = $this->companyService->by_user($request->user_uuid);
        }else{
            $directors = $this->directorService->headquarters();
            $companies = $this->companyService->headquarters();
        }

        // meta datas
        $current_page = $directors->currentPage();
        $max_page = ($directors->lastPage()>$companies->lastPage()?$directors->lastPage():$companies->lastPage());
        $meta = [ 'current_page' => $current_page, 'max_page' => $max_page ];

        return ['directors' => $directors, 'companies' => $companies, 'meta' => $meta];
    }

    /**     @OA\GET(
      *         path="/api/pending/search/{search}",
      *         operationId="list_pending_search",
      *         tags={"Helper"},
      *         summary="List of pending search",
      *         description="List of pending search",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function search(Request $request, $search)
    {
        // if now headquarters then show only belongs to them
        if (!PermissionPolicy::permission($request->user_uuid)){
            $directors = $this->directorService->by_user_search($request->user_uuid, $search);
            $companies = $this->companyService->by_user_search($request->user_uuid, $search);
            return ['directors' => $directors, 'companies' => $companies];
        }

        $directors = $this->directorService->headquarters_search($search);
        $companies = $this->companyService->headquarters_search($search);
        return ['directors' => $directors, 'companies' => $companies];
    }

}
