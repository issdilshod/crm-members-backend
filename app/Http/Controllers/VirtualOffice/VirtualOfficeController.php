<?php

namespace App\Http\Controllers\VirtualOffice;

use App\Http\Controllers\Controller;
use App\Models\VirtualOffice\VirtualOffice;
use App\Policies\PermissionPolicy;
use App\Services\VirtualOffice\VirtualOfficeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class VirtualOfficeController extends Controller
{

    private $virtualOfficeService;

    public function __construct()
    {
        $this->virtualOfficeService = new VirtualOfficeService();
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
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_provider_domain", type="text"),
      *                         @OA\Property(property="vo_provider_username", type="text"),
      *                         @OA\Property(property="vo_provider_password", type="text"),
      *                         @OA\Property(property="street_address", type="text"),
      *                         @OA\Property(property="address_line2", type="text"),
      *                         @OA\Property(property="city", type="text"),
      *                         @OA\Property(property="state", type="text"),
      *                         @OA\Property(property="postal", type="text"),
      *                         @OA\Property(property="country", type="text"),
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
            'vo_provider_name' => '',
            'vo_provider_domain' => '',
            'vo_provider_username' => '',
            'vo_provider_password' => '',
            'street_address' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'postal' => '',
            'country' => '',

            'user_uuid' => ''
        ]);

        /*$check = [];

        $tmpCheck = $this->virtualOfficeService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $virtualOffice = $this->virtualOfficeService->create($validated);

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
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_provider_domain", type="text"),
      *                         @OA\Property(property="vo_provider_username", type="text"),
      *                         @OA\Property(property="vo_provider_password", type="text"),
      *                         @OA\Property(property="street_address", type="text"),
      *                         @OA\Property(property="address_line2", type="text"),
      *                         @OA\Property(property="city", type="text"),
      *                         @OA\Property(property="state", type="text"),
      *                         @OA\Property(property="postal", type="text"),
      *                         @OA\Property(property="country", type="text"),
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
            'vo_provider_name' => '',
            'vo_provider_domain' => '',
            'vo_provider_username' => '',
            'vo_provider_password' => '',
            'street_address' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'postal' => '',
            'country' => ''
        ]);

        /*$check = [];

        $tmpCheck = $this->virtualOfficeService->check_ignore($validated, $virtualOffice->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $virtualOffice = $this->virtualOfficeService->update($virtualOffice, $validated, $request->user_uuid);

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

        $virtualOffice = $this->virtualOfficeService->search($search);

        return $virtualOffice;
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
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_provider_domain", type="text"),
      *                         @OA\Property(property="vo_provider_username", type="text"),
      *                         @OA\Property(property="vo_provider_password", type="text"),
      *                         @OA\Property(property="street_address", type="text"),
      *                         @OA\Property(property="address_line2", type="text"),
      *                         @OA\Property(property="city", type="text"),
      *                         @OA\Property(property="state", type="text"),
      *                         @OA\Property(property="postal", type="text"),
      *                         @OA\Property(property="country", type="text"),
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
            'vo_provider_name' => '',
            'vo_provider_domain' => '',
            'vo_provider_username' => '',
            'vo_provider_password' => '',
            'street_address' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'postal' => '',
            'country' => '',

            'user_uuid' => ''
        ]);

        /*$check = [];

        $tmpCheck = $this->virtualOfficeService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $virtualOffice = $this->virtualOfficeService->pending($validated);

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
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_provider_domain", type="text"),
      *                         @OA\Property(property="vo_provider_username", type="text"),
      *                         @OA\Property(property="vo_provider_password", type="text"),
      *                         @OA\Property(property="street_address", type="text"),
      *                         @OA\Property(property="address_line2", type="text"),
      *                         @OA\Property(property="city", type="text"),
      *                         @OA\Property(property="state", type="text"),
      *                         @OA\Property(property="postal", type="text"),
      *                         @OA\Property(property="country", type="text"),
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.virtual_office.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'vo_provider_name' => '',
            'vo_provider_domain' => '',
            'vo_provider_username' => '',
            'vo_provider_password' => '',
            'street_address' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'postal' => '',
            'country' => ''
        ]);

        $virtualOffice = VirtualOffice::where('uuid', $uuid)->first();

        /*$check = [];

        $tmpCheck = $this->virtualOfficeService->check_ignore($validated, $virtualOffice->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $virtualOffice = $this->virtualOfficeService->pending_update($virtualOffice, $validated, $request->user_uuid);

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
      *                         @OA\Property(property="vo_provider_name", type="text"),
      *                         @OA\Property(property="vo_provider_domain", type="text"),
      *                         @OA\Property(property="vo_provider_username", type="text"),
      *                         @OA\Property(property="vo_provider_password", type="text"),
      *                         @OA\Property(property="street_address", type="text"),
      *                         @OA\Property(property="address_line2", type="text"),
      *                         @OA\Property(property="city", type="text"),
      *                         @OA\Property(property="state", type="text"),
      *                         @OA\Property(property="postal", type="text"),
      *                         @OA\Property(property="country", type="text"),
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
            'vo_provider_name' => '',
            'vo_provider_domain' => '',
            'vo_provider_username' => '',
            'vo_provider_password' => '',
            'street_address' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'postal' => '',
            'country' => ''
        ]);

        $virtualOffice = VirtualOffice::where('uuid', $uuid)->first();

        /*$check = [];

        $tmpCheck = $this->virtualOfficeService->check_ignore($validated, $virtualOffice->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }*/

        $virtualOffice = $this->virtualOfficeService->accept($virtualOffice, $validated, $request->user_uuid);

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
