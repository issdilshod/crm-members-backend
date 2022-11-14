<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Director\Director;
use App\Models\Helper\Address;
use App\Models\Helper\BankAccount;
use App\Models\Helper\Email;
use App\Policies\PermissionPolicy;
use App\Services\Company\CompanyService;
use App\Services\Director\DirectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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
        $pendingOnly = isset($request->pending_only);

        // if now headquarters then show only belongs to them
        if (!PermissionPolicy::permission($request->user_uuid)){
            $directors = $this->directorService->by_user($request->user_uuid, $pendingOnly);
            $companies = $this->companyService->by_user($request->user_uuid, $pendingOnly);
            $summary['directors'] = $this->directorService->summary($request->user_uuid);
            $summary['companies'] = $this->companyService->summary($request->user_uuid);
        }else{
            $directors = $this->directorService->headquarters($pendingOnly);
            $companies = $this->companyService->headquarters($pendingOnly);
            $summary['directors'] = $this->directorService->summary();
            $summary['companies'] = $this->companyService->summary();
        }

        // meta datas
        $current_page = $directors->currentPage();
        $max_page = ($directors->lastPage()>$companies->lastPage()?$directors->lastPage():$companies->lastPage());
        $meta = [ 'current_page' => $current_page, 'max_page' => $max_page ];

        return ['directors' => $directors, 'companies' => $companies, 'meta' => $meta, 'summary' => $summary];
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

    /**     @OA\POST(
      *         path="/api/pending/accept",
      *         operationId="accept_pending",
      *         tags={"Helper"},
      *         summary="Accept pendings",
      *         description="Accept pendings",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"pendings"},
      *                         @OA\Property(property="pendings[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function accept(Request $request)
    {
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'pendings' => 'array|required'
        ]);

        $pendings = [];
        foreach ($validated['pendings'] as $key => $value):
            $director = Director::where('uuid', $value)->first();
            $company = Company::where('uuid', $value)->first();

            if ($director!=null){ // accept director
                $entity = $director->toArray();
                $this->directorService->accept($director, $entity, $request->user_uuid);

                // emails
                Email::where('entity_uuid', $director['uuid'])
                        ->update(['status' => Config::get('common.status.actived')]);
                
                // addresses
                Address::where('entity_uuid', $director['uuid'])
                        ->update(['status' => Config::get('common.status.actived')]);
            }

            if ($company!=null){ // accept company
                $entity = $company->toArray();
                $this->companyService->accept($company, $entity, $request->user_uuid);

                // emails
                Email::where('entity_uuid', $company['uuid'])
                        ->where('status', '!=', Config::get('common.status.deleted'))
                        ->update(['status' => Config::get('common.status.actived')]);

                // address
                Address::where('entity_uuid', $company['uuid'])
                        ->update(['status' => Config::get('common.status.actived')]);

                // bank account
                BankAccount::where('entity_uuid', $company['uuid'])
                            ->update(['status' => Config::get('common.status.actived')]);
            }

            $pendings[] = $value;
        endforeach;
        
        return $pendings;
    }

    /**     @OA\POST(
      *         path="/api/pending/reject",
      *         operationId="reject_pending",
      *         tags={"Helper"},
      *         summary="Reject pendings",
      *         description="Reject pendings",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"pendings"},
      *                         @OA\Property(property="pendings[]", type="text")
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *             @OA\Response(response=409, description="Conflict"),
      *             @OA\Response(response=422, description="Unprocessable Content"),
      *     )
      */
    public function reject(Request $request)
    {
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'pendings' => 'array|required'
        ]);

        $pendings = [];
        foreach ($validated['pendings'] as $key => $value):
            $director = Director::where('uuid', $value)->first();
            $company = Company::where('uuid', $value)->first();

            if ($director!=null){ // accept director
                $this->directorService->reject($value, $request->user_uuid);
            }

            if ($company!=null){ // accept company
                $this->companyService->reject($value, $request->user_uuid);
            }

            $pendings[] = $value;
        endforeach;
        
        return $pendings;   
    }

}
