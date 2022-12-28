<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Director\Director;
use App\Models\Helper\Address;
use App\Models\Helper\BankAccount;
use App\Models\Helper\Email;
use App\Models\VirtualOffice\VirtualOffice;
use App\Policies\PermissionPolicy;
use App\Services\Account\UserService;
use App\Services\Company\CompanyService;
use App\Services\Contact\ContactService;
use App\Services\Director\DirectorService;
use App\Services\VirtualOffice\VirtualOfficeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PendingController extends Controller
{

    private $directorService;
    private $companyService;
    private $virtualOfficesService;
    private $userService;
    private $contactService;

    public function __construct()
    {
        $this->directorService = new DirectorService();
        $this->companyService = new CompanyService();
        $this->virtualOfficesService = new VirtualOfficeService();
        $this->userService = new UserService();
        $this->contactService = new ContactService();
    }
    
    /**     @OA\GET(
      *         path="/api/pending",
      *         operationId="list_pending",
      *         tags={"Helper"},
      *         summary="List of pending",
      *         description="List of pending",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        $filter = ''; $summary_filter = ''; $filter_by_user = '';

        // filters
        if (isset($request->filter)){ $filter = $request->filter; }
        if (isset($request->summary_filter)){ $summary_filter = $request->summary_filter; }
        if (isset($request->filter_by_user)){ $filter_by_user = $request->filter_by_user; }

        // get directors
        $user_uuid = '';
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.view'))){
            $user_uuid = $request->user_uuid;
        }
        $directors = $this->directorService->for_pending($user_uuid, $filter, $summary_filter, $filter_by_user);
        $summary['directors'] = $this->directorService->summary($user_uuid);

        // get companies
        $user_uuid = '';
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.view'))){
            $user_uuid = $request->user_uuid;
        }
        $companies = $this->companyService->for_pending($user_uuid, $filter, $summary_filter, $filter_by_user);
        $summary['companies'] = $this->companyService->summary($user_uuid);

        // get virtual offices
        $user_uuid = '';
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.view'))){
            $user_uuid = $request->user_uuid;
        }
        $virtualOffices = $this->virtualOfficesService->for_pending($user_uuid, $filter, $summary_filter, $filter_by_user);
        $summary['virtual_offices'] = $this->virtualOfficesService->summary($user_uuid);

        // get contacts
        $user_uuid = '';
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.contact.view'))){
            $user_uuid = $request->user_uuid;
        }
        $contacts = $this->contactService->for_pending($user_uuid, $filter, $summary_filter, $filter_by_user);

        // meta data
        $current_page = $directors->currentPage();

        // max page
        $max_page = 0;
        if ($directors->lastPage()>$max_page){ // director max page
            $max_page = $directors->lastPage();
        }

        if ($companies->lastPage()>$max_page){ // company max page
            $max_page = $companies->lastPage();
        }

        if ($virtualOffices->lastPage()>$max_page){ // virtual offices max page
            $max_page = $virtualOffices->lastPage();
        }

        if ($contacts->lastPage()>$max_page){ // contacts max page
            $max_page = $contacts->lastPage();
        }

        $meta = [ 'current_page' => $current_page, 'max_page' => $max_page ];

        return [
            'directors' => $directors, 
            'companies' => $companies, 
            'virtual_offices' => $virtualOffices,
            'contacts' => $contacts,
            
            'meta' => $meta, 
            'summary' => $summary
        ];
    }

    /**     @OA\GET(
      *         path="/api/pending/search?q=",
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
    public function search(Request $request)
    {
        // get query
        $search = '';
        if (isset($request->q)){ $search = $request->q; }
        
        $user_uuid = '';
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.view'))){
            $user_uuid = $request->user_uuid;
        }
        $directors = $this->directorService->for_pending_search($user_uuid, $search);
        
        // get related
        $director_related = $this->companyService->for_pending_related($directors);
        
        $user_uuid = '';
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.company.view'))){
            $user_uuid = $request->user_uuid;
        }
        $companies = $this->companyService->for_pending_search($user_uuid, $search);

        // get related
        $company_related = $this->directorService->for_pending_related($companies);

        // merge related
        $companies = $companies->merge($director_related);
        $directors = $directors->merge($company_related);

        $companies = $companies->unique('uuid')->values()->all();
        $directors = $directors->unique('uuid')->values()->all();

        return ['directors' => $directors, 'companies' => $companies];
    }

    /**     @OA\GET(
      *         path="/api/pending/duplicate",
      *         operationId="list_pending_duplicate",
      *         tags={"Helper"},
      *         summary="List of pending duplicate",
      *         description="List of pending deplicate",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function duplicate(Request $request)
    {

        $user_uuid = '';

        // director get 
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.director.view'))){
            $user_uuid = $request->user_uuid;
        }
        $directors = $this->directorService->for_pending_duplicate($user_uuid);

        // meta data
        $current_page = $directors->currentPage();

        // max page
        $max_page = 0;
        if ($directors->lastPage()>$max_page){ // director max page
            $max_page = $directors->lastPage();
        }

        $meta = [ 'current_page' => $current_page, 'max_page' => $max_page ];

        return ['directors' => $directors, 'meta' => $meta];
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
            $director = Director::where('uuid', $value)->where('status', '!=', Config::get('common.status.deleted'))->first();
            $company = Company::where('uuid', $value)->where('status', '!=', Config::get('common.status.deleted'))->first();
            $virtualOffice = VirtualOffice::where('uuid', $value)->where('status', '!=', Config::get('common.status.deleted'))->first();

            if ($director!=null){ // accept director
                $entity = $director->toArray();
                $director = $this->directorService->accept($director, $entity, $request->user_uuid);

                // emails
                Email::where('entity_uuid', $director['uuid'])
                        ->where('status', '!=', Config::get('common.status.deleted'))
                        ->update(['status' => Config::get('common.status.actived')]);
                
                // addresses
                Address::where('entity_uuid', $director['uuid'])
                        ->where('status', '!=', Config::get('common.status.deleted'))
                        ->update(['status' => Config::get('common.status.actived')]);

                $value = $director;
            }

            if ($company!=null){ // accept company
                $entity = $company->toArray();
                $company = $this->companyService->accept($company, $entity, $request->user_uuid);

                // emails
                Email::where('entity_uuid', $company['uuid'])
                        ->where('status', '!=', Config::get('common.status.deleted'))
                        ->update(['status' => Config::get('common.status.actived')]);

                // address
                Address::where('entity_uuid', $company['uuid'])
                        ->where('status', '!=', Config::get('common.status.deleted'))
                        ->update(['status' => Config::get('common.status.actived')]);

                // bank account
                BankAccount::where('entity_uuid', $company['uuid'])
                        ->where('status', '!=', Config::get('common.status.deleted'))
                        ->update(['status' => Config::get('common.status.actived')]);

                $value = $company;
            }

            if ($virtualOffice!=null){ // accept virtual office
                $entity = $virtualOffice->toArray();
                $virtualOffice = $this->virtualOfficesService->accept($virtualOffice, $entity, $request->user_uuid);

                // address
                Address::where('entity_uuid', $virtualOffice['uuid'])
                        ->where('status', '!=', Config::get('common.status.deleted'))
                        ->update(['status' => Config::get('common.status.actived')]);

                $value = $virtualOffice;
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
            $virtualOffice = VirtualOffice::where('uuid', $value)->first();

            if ($director!=null){ // accept director
                $value = $this->directorService->reject($value, $request->user_uuid);
            }

            if ($company!=null){ // accept company
                $value = $this->companyService->reject($value, $request->user_uuid);
            }

            if ($virtualOffice!=null){
                $value = $this->virtualOfficesService->reject($value, $request->user_uuid);
            }

            $pendings[] = $value;
        endforeach;
        
        return $pendings;   
    }

    /**     @OA\GET(
      *         path="/api/pending/users",
      *         operationId="list_of_users_for_pending",
      *         tags={"Helper"},
      *         summary="List of users",
      *         description="List of users to create the chat",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function users(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $users = $this->userService->all();
        return $users;
    }

}
