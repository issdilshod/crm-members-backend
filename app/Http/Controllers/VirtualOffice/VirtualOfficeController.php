<?php

namespace App\Http\Controllers\VirtualOffice;

use App\Http\Controllers\Controller;
use App\Models\VirtualOffice\VirtualOffice;
use App\Policies\PermissionPolicy;
use App\Services\Helper\AddressService;
use App\Services\Helper\RejectReasonService;
use App\Services\VirtualOffice\VirtualOfficeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class VirtualOfficeController extends Controller
{

    private $virtualOfficeService;
    private $addressService;
    private $rejectReasonService;

    public function __construct()
    {
        $this->virtualOfficeService = new VirtualOfficeService();
        $this->addressService = new AddressService();
        $this->rejectReasonService = new RejectReasonService();
    }
    
    /**     @OA\GET(
      *         path="/api/virtual-office",
      *         operationId="list_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="List of virtual office",
      *         description="List of virtual office",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $virtualOffices = $this->virtualOfficeService->all();

        return $virtualOffices;
    }

    /**     @OA\POST(
      *         path="/api/virtual-office",
      *         operationId="post_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="Add virtual office",
      *         description="Add virtual office",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         
      *                         @OA\Property(property="vo_active", type="text"),
      *                         @OA\Property(property="vo_signer_uuid", type="text"),
      *                         @OA\Property(property="vo_signer_company_uuid", type="text"),
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_website", type="text"),
      *                         @OA\Property(property="vo_provider_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_name", type="text"),
      *                         @OA\Property(property="vo_contact_person_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_email", type="text"),
      *                         @OA\Property(property="online_account", type="text"),
      *                         @OA\Property(property="online_email", type="text"),
      *                         @OA\Property(property="online_account_username", type="text"),
      *                         @OA\Property(property="online_account_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="autopay", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *                         @OA\Property(property="monthly_payment_amount", type="text"),
      *
      *                         @OA\Property(property="agreement_terms", type="text"),
      *
      *                         @OA\Property(property="contract", type="text"),
      *                         @OA\Property(property="contract_terms", type="text"),
      *                         @OA\Property(property="contract_terms_notes", type="text"),
      *                         @OA\Property(property="contract_effective_date", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'vo_active' => '',
            'vo_signer_uuid' => '',
            'vo_signer_company_uuid' => '',
            'vo_provider_name' => '',
            'vo_website' => '',
            'vo_provider_phone_number' => '',
            'vo_contact_person_name' => '',
            'vo_contact_person_phone_number' => '',
            'vo_contact_person_email' => '',
            'online_account' => '',
            'online_email' => '',
            'online_account_username' => '',
            'online_account_password' => '',

            'card_on_file' => '',
            'autopay' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',
            'monthly_payment_amount' => '',

            'agreement_terms' => '',

            'contract' => '',
            'contract_terms' => '',
            'contract_terms_notes' => '',
            'contract_effective_date' => '',
            
            'addresses' => 'array',

            'user_uuid' => ''
        ]);

        // check
        $check = [];

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key, '', 'virtual_offices');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        $virtualOffice = $this->virtualOfficeService->create($validated);

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $virtualOffice->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        return $virtualOffice;
    }

    /**     @OA\GET(
      *         path="/api/virtual-office/{uuid}",
      *         operationId="get_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="Get virtual office",
      *         description="Get virtual office",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="virtual office uuid",
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
    public function show(Request $request, VirtualOffice $virtualOffice)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $virtualOffice = $this->virtualOfficeService->one($virtualOffice);

        return $virtualOffice;
    }

    /**     @OA\PUT(
      *         path="/api/virtual-office/{uuid}",
      *         operationId="update_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="Update virtual office",
      *         description="Update virtual office",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="virtual office uuid",
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
      *                         @OA\Property(property="vo_active", type="text"),
      *                         @OA\Property(property="vo_signer_uuid", type="text"),
      *                         @OA\Property(property="vo_signer_company_uuid", type="text"),
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_website", type="text"),
      *                         @OA\Property(property="vo_provider_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_name", type="text"),
      *                         @OA\Property(property="vo_contact_person_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_email", type="text"),
      *                         @OA\Property(property="online_account", type="text"),
      *                         @OA\Property(property="online_email", type="text"),
      *                         @OA\Property(property="online_account_username", type="text"),
      *                         @OA\Property(property="online_account_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="autopay", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *                         @OA\Property(property="monthly_payment_amount", type="text"),
      *
      *                         @OA\Property(property="agreement_terms", type="text"),
      *
      *                         @OA\Property(property="contract", type="text"),
      *                         @OA\Property(property="contract_terms", type="text"),
      *                         @OA\Property(property="contract_terms_notes", type="text"),
      *                         @OA\Property(property="contract_effective_date", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
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
    public function update(Request $request, VirtualOffice $virtualOffice)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'vo_active' => '',
            'vo_signer_uuid' => '',
            'vo_signer_company_uuid' => '',
            'vo_provider_name' => '',
            'vo_website' => '',
            'vo_provider_phone_number' => '',
            'vo_contact_person_name' => '',
            'vo_contact_person_phone_number' => '',
            'vo_contact_person_email' => '',
            'online_account' => '',
            'online_email' => '',
            'online_account_username' => '',
            'online_account_password' => '',

            'card_on_file' => '',
            'autopay' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',
            'monthly_payment_amount' => '',

            'agreement_terms' => '',

            'contract' => '',
            'contract_terms' => '',
            'contract_terms_notes' => '',
            'contract_effective_date' => '',

            'addresses' => 'array',
            
            'user_uuid' => ''
        ]);

        // check
        $check = [];

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key, $virtualOffice->uuid, 'virtual_offices');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        $virtualOffice = $this->virtualOfficeService->update($virtualOffice, $validated);

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $virtualOffice->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        return $virtualOffice;
    }

    /**     @OA\DELETE(
      *         path="/api/virtual-office/{uuid}",
      *         operationId="delete_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="Delete virtual office",
      *         description="Delete virtual office",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="virtual office uuid",
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
    public function destroy(Request $request, VirtualOffice $virtualOffice)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.delete'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $this->virtualOfficeService->delete($virtualOffice);
    }

    /**     @OA\GET(
      *         path="/api/virtual-office-search/{search}",
      *         operationId="get_virtual_office_search",
      *         tags={"Virtual Office"},
      *         summary="Get virtual office search",
      *         description="Get virtual office search",
      *             @OA\Parameter(
      *                 name="search",
      *                 in="path",
      *                 description="virtual office search",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $virtualOffices = $this->virtualOfficeService->search($request->user_uuid, $search);

        return $virtualOffices;
    }

    /**     @OA\POST(
      *         path="/api/virtual-office-pending",
      *         operationId="pending_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="Pending virtual office",
      *         description="Pending virtual office",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={},
      *                         
      *                         @OA\Property(property="vo_active", type="text"),
      *                         @OA\Property(property="vo_signer_uuid", type="text"),
      *                         @OA\Property(property="vo_signer_company_uuid", type="text"),
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_website", type="text"),
      *                         @OA\Property(property="vo_provider_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_name", type="text"),
      *                         @OA\Property(property="vo_contact_person_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_email", type="text"),
      *                         @OA\Property(property="online_account", type="text"),
      *                         @OA\Property(property="online_email", type="text"),
      *                         @OA\Property(property="online_account_username", type="text"),
      *                         @OA\Property(property="online_account_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="autopay", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *                         @OA\Property(property="monthly_payment_amount", type="text"),
      *
      *                         @OA\Property(property="agreement_terms", type="text"),
      *
      *                         @OA\Property(property="contract", type="text"),
      *                         @OA\Property(property="contract_terms", type="text"),
      *                         @OA\Property(property="contract_terms_notes", type="text"),
      *                         @OA\Property(property="contract_effective_date", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'vo_active' => '',
            'vo_signer_uuid' => '',
            'vo_signer_company_uuid' => '',
            'vo_provider_name' => '',
            'vo_website' => '',
            'vo_provider_phone_number' => '',
            'vo_contact_person_name' => '',
            'vo_contact_person_phone_number' => '',
            'vo_contact_person_email' => '',
            'online_account' => '',
            'online_email' => '',
            'online_account_username' => '',
            'online_account_password' => '',

            'card_on_file' => '',
            'autopay' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',
            'monthly_payment_amount' => '',
            
            'agreement_terms' => '',

            'contract' => '',
            'contract_terms' => '',
            'contract_terms_notes' => '',
            'contract_effective_date' => '',

            // addresses
            'addresses' => 'array',

            'user_uuid' => ''
        ]);

        // check
        $check = [];

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key, '', 'virtual_offices');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        $virtualOffice = $this->virtualOfficeService->pending($validated);

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $virtualOffice->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        return $virtualOffice;
    }

    /**     @OA\PUT(
      *         path="/api/virtual-office-pending-update/{uuid}",
      *         operationId="pending_update_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="Pending update virtual office",
      *         description="Pending update virtual office",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="virtual office uuid",
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
      *                         @OA\Property(property="vo_active", type="text"),
      *                         @OA\Property(property="vo_signer_uuid", type="text"),
      *                         @OA\Property(property="vo_signer_company_uuid", type="text"),
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_website", type="text"),
      *                         @OA\Property(property="vo_provider_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_name", type="text"),
      *                         @OA\Property(property="vo_contact_person_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_email", type="text"),
      *                         @OA\Property(property="online_account", type="text"),
      *                         @OA\Property(property="online_email", type="text"),
      *                         @OA\Property(property="online_account_username", type="text"),
      *                         @OA\Property(property="online_account_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="autopay", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *                         @OA\Property(property="monthly_payment_amount", type="text"),
      *
      *                         @OA\Property(property="agreement_terms", type="text"),
      *
      *                         @OA\Property(property="contract", type="text"),
      *                         @OA\Property(property="contract_terms", type="text"),
      *                         @OA\Property(property="contract_terms_notes", type="text"),
      *                         @OA\Property(property="contract_effective_date", type="text"),
      *
      *                         @OA\Property(property="addresses", type="text"),
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
        $virtualOffice = VirtualOffice::where('uuid', $uuid)->first();

        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }else if ($virtualOffice->user_uuid!=$request->user_uuid){ // if double update other user
                if ($virtualOffice->status!=Config::get('common.status.actived')){ // not active entity
                    return response()->json([ 'data' => 'Not Authorized' ], 403);
                }
            }
        }

        $validated = $request->validate([
            'vo_active' => '',
            'vo_signer_uuid' => '',
            'vo_signer_company_uuid' => '',
            'vo_provider_name' => '',
            'vo_website' => '',
            'vo_provider_phone_number' => '',
            'vo_contact_person_name' => '',
            'vo_contact_person_phone_number' => '',
            'vo_contact_person_email' => '',
            'online_account' => '',
            'online_email' => '',
            'online_account_username' => '',
            'online_account_password' => '',

            'card_on_file' => '',
            'autopay' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',
            'monthly_payment_amount' => '',
            
            'agreement_terms' => '',

            'contract' => '',
            'contract_terms' => '',
            'contract_terms_notes' => '',
            'contract_effective_date' => '',

            // addresses
            'addresses' => 'array',

            'user_uuid' => ''
        ]);

        // check
        $check = [];

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key, $virtualOffice->uuid, 'virtual_offices');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        $virtualOffice = $this->virtualOfficeService->pending_update($virtualOffice, $validated, $request->user_uuid);

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $virtualOffice->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        return $virtualOffice;
    }

    /**     @OA\PUT(
      *         path="/api/virtual-office-accept/{uuid}",
      *         operationId="accept_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="Accept virtual office",
      *         description="Accept virtual office",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="virtual office uuid",
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
      *                         @OA\Property(property="vo_active", type="text"),    
      *                         @OA\Property(property="vo_signer_uuid", type="text"),
      *                         @OA\Property(property="vo_signer_company_uuid", type="text"),
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_website", type="text"),
      *                         @OA\Property(property="vo_provider_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_name", type="text"),
      *                         @OA\Property(property="vo_contact_person_phone_number", type="text"),
      *                         @OA\Property(property="vo_contact_person_email", type="text"),
      *                         @OA\Property(property="online_account", type="text"),
      *                         @OA\Property(property="online_email", type="text"),
      *                         @OA\Property(property="online_account_username", type="text"),
      *                         @OA\Property(property="online_account_password", type="text"),
      *
      *                         @OA\Property(property="card_on_file", type="text"),
      *                         @OA\Property(property="autopay", type="text"),
      *                         @OA\Property(property="card_last_four_digit", type="text"),
      *                         @OA\Property(property="card_holder_name", type="text"),
      *                         @OA\Property(property="monthly_payment_amount", type="text"),
      *
      *                         @OA\Property(property="agreement_terms", type="text"),
      *
      *                         @OA\Property(property="contract", type="text"),
      *                         @OA\Property(property="contract_terms", type="text"),
      *                         @OA\Property(property="contract_terms_notes", type="text"),
      *                         @OA\Property(property="contract_effective_date", type="text"),
      *
      *                         @OA\Property(property="addresses[]", type="text"),
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'vo_active' => '',
            'vo_signer_uuid' => '',
            'vo_signer_company_uuid' => '',
            'vo_provider_name' => '',
            'vo_website' => '',
            'vo_provider_phone_number' => '',
            'vo_contact_person_name' => '',
            'vo_contact_person_phone_number' => '',
            'vo_contact_person_email' => '',
            'online_account' => '',
            'online_email' => '',
            'online_account_username' => '',
            'online_account_password' => '',

            'card_on_file' => '',
            'autopay' => '',
            'card_last_four_digit' => '',
            'card_holder_name' => '',
            'monthly_payment_amount' => '',
            
            'agreement_terms' => '',

            'contract' => '',
            'contract_terms' => '',
            'contract_terms_notes' => '',
            'contract_effective_date' => '',

            // addresses
            'addresses' => 'array',

            'user_uuid' => ''
        ]);

        $virtualOffice = VirtualOffice::where('uuid', $uuid)->first();

        // check
        $check = [];

        // addresses check
        if (isset($validated['addresses'])){
            foreach($validated['addresses'] AS $key => $value):
                $tmpCheck = $this->addressService->check($value, $key, $virtualOffice->uuid, 'virtual_offices');
                $check = array_merge($check, $tmpCheck);
            endforeach;
        }

        if (count($check)>0){
            return response()->json(['data' => $check], 409);
        }

        $virtualOffice = $this->virtualOfficeService->accept($virtualOffice, $validated);

        // addresses
        if (isset($validated['addresses'])){
            foreach ($validated['addresses'] AS $key => $value):
                $value['entity_uuid'] = $virtualOffice->uuid;
                $this->addressService->save($value);
            endforeach;
        }

        return $virtualOffice;
    }

    /**     @OA\PUT(
      *         path="/api/virtual-office-reject/{uuid}",
      *         operationId="reject_virtual_office",
      *         tags={"Virtual Office"},
      *         summary="Reject virtual office",
      *         description="Reject virtual office",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="virtual office uuid",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.accept'))){
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

        $this->virtualOfficeService->reject($uuid, $request->user_uuid);
    }

    /**     @OA\GET(
      *         path="/api/virtual-office-permission",
      *         operationId="virtual_office_permission",
      *         tags={"Virtual Office"},
      *         summary="Get virtual office permission of user",
      *         description="Get virtual office permission of user",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *     )
      */
    public function permission(Request $request)
    {
        $permissions = [];

        // permission
        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.view'))){
            $permissions[] = Config::get('common.permission.virtual_office.view');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.store'))){
            $permissions[] = Config::get('common.permission.virtual_office.store');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.save'))){
            $permissions[] = Config::get('common.permission.virtual_office.save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.delete'))){
            $permissions[] = Config::get('common.permission.virtual_office.delete');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.accept'))){
            $permissions[] = Config::get('common.permission.virtual_office.accept');
        }

        return $permissions;
    }
}
