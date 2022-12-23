<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\FutureCompany;
use App\Policies\PermissionPolicy;
use App\Services\Company\FutureCompanyService;
use App\Services\Helper\RejectReasonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class FutureCompanyController extends Controller
{

    private $futureCompanyService;
    private $rejectReasonService;

    public function __construct()
    {
        $this->futureCompanyService = new FutureCompanyService();
        $this->rejectReasonService = new RejectReasonService();
    }
    
    /**     @OA\GET(
      *         path="/api/future-company",
      *         operationId="list_future_company",
      *         tags={"Company"},
      *         summary="List of future company",
      *         description="List of future company",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $futureCompanies = $this->futureCompanyService->all();

        return $futureCompanies;
    }

    /**     @OA\POST(
      *         path="/api/future-company",
      *         operationId="post_future_company",
      *         tags={"Company"},
      *         summary="Add future company",
      *         description="Add future company",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="virtual_office_uuid", type="text"),
      *                         @OA\Property(property="revival_date", type="text"),
      *                         @OA\Property(property="revival_fee", type="text"),
      *                         @OA\Property(property="future_website_link", type="text"),
      *                         @OA\Property(property="recommended_director_uuid", type="text"),
      *                         @OA\Property(property="revived", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *                         @OA\Property(property="comment", type="text"),
      *                         @OA\Property(property="files[]", type="binary"),
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
    public function store(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => '',
            'incorporation_state_uuid' => '',
            'doing_business_in_state_uuid' => '',
            'virtual_office_uuid' => '',
            'revival_date' => '',
            'revival_fee' => '',
            'future_website_link' => '',
            'recommended_director_uuid' => '',
            'revived' => '',
            'db_report_number' => '',
            'comment' => '',

            'user_uuid' => ''
        ]);

        /*$check = [];

        $tmpCheck = $this->futureCompanyService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $futureCompany = $this->futureCompanyService->create($validated);

        // upload files

        return $futureCompany;
    }

    /**     @OA\GET(
      *         path="/api/future-company/{uuid}",
      *         operationId="get_future_company",
      *         tags={"Company"},
      *         summary="Get future company",
      *         description="Get future company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Request $request, FutureCompany $futureCompany)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $futureCompany = $this->futureCompanyService->one($futureCompany);

        return $futureCompany;
    }

    /**     @OA\PUT(
      *         path="/api/future-company/{uuid}",
      *         operationId="update_future_company",
      *         tags={"Company"},
      *         summary="Update future company",
      *         description="Update future company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="virtual_office_uuid", type="text"),
      *                         @OA\Property(property="revival_date", type="text"),
      *                         @OA\Property(property="revival_fee", type="text"),
      *                         @OA\Property(property="future_website_link", type="text"),
      *                         @OA\Property(property="recommended_director_uuid", type="text"),
      *                         @OA\Property(property="revived", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *                         @OA\Property(property="comment", type="text"),
      *                         @OA\Property(property="files[]", type="binary"),
      *                         @OA\Property(property="files_to_delete[]", type="text")
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
    public function update(Request $request, FutureCompany $futureCompany)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => '',
            'incorporation_state_uuid' => '',
            'doing_business_in_state_uuid' => '',
            'virtual_office_uuid' => '',
            'revival_date' => '',
            'revival_fee' => '',
            'future_website_link' => '',
            'recommended_director_uuid' => '',
            'revived' => '',
            'db_report_number' => '',
            'comment' => '',

            'files_to_delete' => 'array'
        ]);

        /*$check = [];

        $tmpCheck = $this->futureCompanyService->check_ignore($validated, $futureCompany->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $futureCompany = $this->futureCompanyService->update($futureCompany, $validated, $request->user_uuid);

        // files upload & files delete

        return $futureCompany;
    }

    /**     @OA\DELETE(
      *         path="/api/future-company/{uuid}",
      *         operationId="delete_future_company",
      *         tags={"Company"},
      *         summary="Delete future company",
      *         description="Delete future company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */    
    public function destroy(Request $request, FutureCompany $futureCompany)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.delete'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $this->futureCompanyService->delete($futureCompany);
    }

    /**     @OA\GET(
      *         path="/api/future-company-search/{search}",
      *         operationId="get_future_company_search",
      *         tags={"Company"},
      *         summary="Get future company search",
      *         description="Get future company search",
      *             @OA\Parameter(
      *                 name="search",
      *                 in="path",
      *                 description="future company search",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="text"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function search(Request $request, $search)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $futureCompanies = $this->futureCompanyService->search($search);

        return $futureCompanies;
    }

    /**     @OA\POST(
      *         path="/api/future-company-pending",
      *         operationId="pending_future_company",
      *         tags={"Company"},
      *         summary="Pending future company",
      *         description="Pending future company",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="virtual_office_uuid", type="text"),
      *                         @OA\Property(property="revival_date", type="text"),
      *                         @OA\Property(property="revival_fee", type="text"),
      *                         @OA\Property(property="future_website_link", type="text"),
      *                         @OA\Property(property="recommended_director_uuid", type="text"),
      *                         @OA\Property(property="revived", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *                         @OA\Property(property="comment", type="text"),
      *                         @OA\Property(property="files[]", type="binary"),
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
    public function pending(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => '',
            'incorporation_state_uuid' => '',
            'doing_business_in_state_uuid' => '',
            'virtual_office_uuid' => '',
            'revival_date' => '',
            'revival_fee' => '',
            'future_website_link' => '',
            'recommended_director_uuid' => '',
            'revived' => '',
            'db_report_number' => '',
            'comment' => '',

            'user_uuid' => ''
        ]);

        /*$check = [];

        $tmpCheck = $this->futureCompanyService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $futureCompany = $this->futureCompanyService->pending($validated);

        // upload files

        return $futureCompany;
    }

    /**     @OA\PUT(
      *         path="/api/future-company-pending-update/{uuid}",
      *         operationId="pending_update_future_company",
      *         tags={"Company"},
      *         summary="Pending update future company",
      *         description="Pending update future company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="virtual_office_uuid", type="text"),
      *                         @OA\Property(property="revival_date", type="text"),
      *                         @OA\Property(property="revival_fee", type="text"),
      *                         @OA\Property(property="future_website_link", type="text"),
      *                         @OA\Property(property="recommended_director_uuid", type="text"),
      *                         @OA\Property(property="revived", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *                         @OA\Property(property="comment", type="text"),
      *                         @OA\Property(property="files[]", type="binary"),
      *                         @OA\Property(property="files_to_delete[]", type="text")
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
    public function pending_update(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => '',
            'incorporation_state_uuid' => '',
            'doing_business_in_state_uuid' => '',
            'virtual_office_uuid' => '',
            'revival_date' => '',
            'revival_fee' => '',
            'future_website_link' => '',
            'recommended_director_uuid' => '',
            'revived' => '',
            'db_report_number' => '',
            'comment' => '',

            'files_to_delete' => 'array'
        ]);

        $futureCompany = FutureCompany::where('uuid', $uuid)->first();

        /*$check = [];

        $tmpCheck = $this->futureCompanyService->check_ignore($validated, $futureCompany->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $futureCompany = $this->futureCompanyService->pending_update($futureCompany, $validated, $request->user_uuid);

        // files upload & files delete

        return $futureCompany;
    }

    /**     @OA\PUT(
      *         path="/api/future-company-accept/{uuid}",
      *         operationId="accept_future_company",
      *         tags={"Company"},
      *         summary="Accept future company",
      *         description="Accept future company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="incorporation_state_uuid", type="text"),
      *                         @OA\Property(property="doing_business_in_state_uuid", type="text"),
      *                         @OA\Property(property="virtual_office_uuid", type="text"),
      *                         @OA\Property(property="revival_date", type="text"),
      *                         @OA\Property(property="revival_fee", type="text"),
      *                         @OA\Property(property="future_website_link", type="text"),
      *                         @OA\Property(property="recommended_director_uuid", type="text"),
      *                         @OA\Property(property="revived", type="text"),
      *                         @OA\Property(property="db_report_number", type="text"),
      *                         @OA\Property(property="comment", type="text"),
      *                         @OA\Property(property="files[]", type="binary"),
      *                         @OA\Property(property="files_to_delete[]", type="text")
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
    public function accept(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => '',
            'incorporation_state_uuid' => '',
            'doing_business_in_state_uuid' => '',
            'virtual_office_uuid' => '',
            'revival_date' => '',
            'revival_fee' => '',
            'future_website_link' => '',
            'recommended_director_uuid' => '',
            'revived' => '',
            'db_report_number' => '',
            'comment' => '',

            'files_to_delete' => 'array'
        ]);

        $futureCompany = FutureCompany::where('uuid', $uuid)->first();

        /*$check = [];

        $tmpCheck = $this->futureCompanyService->check_ignore($validated, $futureCompany->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $futureCompany = $this->futureCompanyService->accept($futureCompany, $validated, $request->user_uuid);

        // files upload & files delete

        return $futureCompany;
    }

    /**     @OA\PUT(
      *         path="/api/future-company-reject/{uuid}",
      *         operationId="reject_future_company",
      *         tags={"Company"},
      *         summary="Reject future company",
      *         description="Reject future company",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future company uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         required={},
      *                         @OA\Property(property="description", type="text"),
      *                     ),
      *                 ),
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=403, description="Not Autorized"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function reject(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'description' => ''
        ]);

        // reject reason
        if (isset($validated['description'])){
            $this->rejectReasonService->create([
                'entity_uuid' => $uuid,
                'description' => $validated['description']
            ]);
        }

        $this->futureCompanyService->reject($uuid, $request->user_uuid);
    }

    /**     @OA\GET(
      *         path="/api/future-company-permission",
      *         operationId="future_company_permission",
      *         tags={"Company"},
      *         summary="Get future company permission of user",
      *         description="Get future company permission of user",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *     )
      */
    public function permission(Request $request)
    {
        $permissions = [];

        // permission
        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.view'))){
            $permissions[] = Config::get('common.permission.future_company.view');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.store'))){
            $permissions[] = Config::get('common.permission.future_company.store');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.save'))){
            $permissions[] = Config::get('common.permission.future_company.save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.delete'))){
            $permissions[] = Config::get('common.permission.future_company.delete');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_company.accept'))){
            $permissions[] = Config::get('common.permission.future_company.accept');
        }

        return $permissions;
    }

}
