<?php

namespace App\Http\Controllers\FutureWebsite;

use App\Http\Controllers\Controller;
use App\Models\FutureWebsite\FutureWebsite;
use App\Policies\PermissionPolicy;
use App\Services\FutureWebsite\FutureWebsiteService;
use App\Services\Helper\RejectReasonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class FutureWebsiteController extends Controller
{
    private $futureWebsiteService;
    private $rejectReasonService;

    public function __construct()
    {
        $this->futureWebsiteService = new FutureWebsiteService();
        $this->rejectReasonService = new RejectReasonService();
    }
    
    /**     @OA\GET(
      *         path="/api/future-websites",
      *         operationId="list_future_websites",
      *         tags={"Future Websites"},
      *         summary="List of future websites",
      *         description="List of future websites",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $futureWebsites = $this->futureWebsiteService->all();

        return $futureWebsites;
    }

    /**     @OA\POST(
      *         path="/api/future-websites",
      *         operationId="post_future_websites",
      *         tags={"Future Websites"},
      *         summary="Add future websites",
      *         description="Add future websites",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"sic_code_uuid", "link"},
      *                         
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="link", type="text")
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required',
            'user_uuid' => ''
        ]);

        $check = [];

        $tmpCheck = $this->futureWebsiteService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $futureWebsite = $this->futureWebsiteService->create($validated);

        return $futureWebsite;
    }

    /**     @OA\GET(
      *         path="/api/future-websites/{uuid}",
      *         operationId="get_future_websites",
      *         tags={"Future Websites"},
      *         summary="Get future websites",
      *         description="Get future websites",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future websites uuid",
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
    public function show(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $futureWebsite = FutureWebsite::where('uuid', $uuid)->first();

        $futureWebsite = $this->futureWebsiteService->one($futureWebsite);

        return $futureWebsite;
    }

    /**     @OA\PUT(
      *         path="/api/future-websites/{uuid}",
      *         operationId="update_future_websites",
      *         tags={"Future Websites"},
      *         summary="Update future websites",
      *         description="Update future websites",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future websites uuid",
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
      *                         required={"sic_code_uuid", "link"},
      *
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="link", type="text")
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
    public function update(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.store'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required'
        ]);

        $futureWebsite = FutureWebsite::where('uuid', $uuid)->first();

        $check = [];

        $tmpCheck = $this->futureWebsiteService->check_ignore($validated, $futureWebsite->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $futureWebsite = $this->futureWebsiteService->update($futureWebsite, $validated, $request->user_uuid);

        return $futureWebsite;
    }

    /**     @OA\DELETE(
      *         path="/api/future-websites/{uuid}",
      *         operationId="delete_future_websites",
      *         tags={"Future Websites"},
      *         summary="Delete future websites",
      *         description="Delete future websites",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future websites uuid",
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
    public function destroy(Request $request, FutureWebsite $futureWebsite)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){ // if not headquarter
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.delete'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $this->futureWebsiteService->delete($futureWebsite);
    }

    /**     @OA\POST(
      *         path="/api/future-websites-pending",
      *         operationId="pending_future_websites",
      *         tags={"Future Websites"},
      *         summary="Pending future websites",
      *         description="Pending future websites",
      *             @OA\RequestBody(
      *                 @OA\JsonContent(),
      *                 @OA\MediaType(
      *                     mediaType="multipart/form-data",
      *                     @OA\Schema(
      *                         type="object",
      *                         required={"sic_code_uuid", "link"},
      *
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="link", type="text")
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required',
            'user_uuid' => ''
        ]);

        $check = [];

        $tmpCheck = $this->futureWebsiteService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $futureWebsite = $this->futureWebsiteService->pending($validated);

        return $futureWebsite;
    }

    /**     @OA\PUT(
      *         path="/api/future-websites-pending-update/{uuid}",
      *         operationId="pending_update_future_websites",
      *         tags={"Future Websites"},
      *         summary="Pending update future websites",
      *         description="Pending update future websites",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future websites uuid",
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
      *                         required={"sic_code_uuid", "link"},
      *
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="link", type="text")
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.save'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required'
        ]);

        $check = [];

        $futureWebsite = FutureWebsite::where('uuid', $uuid)->first();

        $tmpCheck = $this->futureWebsiteService->check_ignore($validated, $futureWebsite->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $futureWebsite = $this->futureWebsiteService->pending_update($futureWebsite, $validated, $request->user_uuid);

        return $futureWebsite;
    }

    /**     @OA\PUT(
      *         path="/api/future-websites-accept/{uuid}",
      *         operationId="accept_future_websites",
      *         tags={"Future Websites"},
      *         summary="Accept future websites",
      *         description="Accept future websites",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future websites uuid",
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
      *                         required={"sic_code_uuid", "link"},
      *
      *                         @OA\Property(property="sic_code_uuid", type="text"),
      *                         @OA\Property(property="link", type="text")
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.accept'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required'
        ]);

        $check = [];

        $futureWebsite = FutureWebsite::where('uuid', $uuid)->first();

        $tmpCheck = $this->futureWebsiteService->check_ignore($validated, $futureWebsite->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $futureWebsite = $this->futureWebsiteService->accept($futureWebsite, $validated, $request->user_uuid);

        return $futureWebsite;
    }

    /**     @OA\PUT(
      *         path="/api/future-websites-reject/{uuid}",
      *         operationId="reject_future_websites",
      *         tags={"Future Websites"},
      *         summary="Reject future websites",
      *         description="Reject future websites",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="future websies uuid",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.accept'))){
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

        $this->futureWebsiteService->reject($uuid, $request->user_uuid);
    }

    /**     @OA\GET(
      *         path="/api/future-websites-search/{search}",
      *         operationId="get_future_websites_search",
      *         tags={"Future Websites"},
      *         summary="Get future websites search",
      *         description="Get future websites search",
      *             @OA\Parameter(
      *                 name="search",
      *                 in="path",
      *                 description="future websites search",
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
            if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.view'))){
                return response()->json([ 'data' => 'Not Authorized' ], 403);
            }
        }

        $futureWebsite = $this->futureWebsiteService->search($search);

        return $futureWebsite;
    }

    /**     @OA\GET(
      *         path="/api/future-websites-permission",
      *         operationId="future_websites_permission",
      *         tags={"Future Websites"},
      *         summary="Get future websites permission of user",
      *         description="Get future websites permission of user",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *     )
      */
    public function permission(Request $request)
    {
        $permissions = [];

        // permission
        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.view'))){
            $permissions[] = Config::get('common.permission.future_website.view');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.store'))){
            $permissions[] = Config::get('common.permission.future_website.store');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.save'))){
            $permissions[] = Config::get('common.permission.future_website.save');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.delete'))){
            $permissions[] = Config::get('common.permission.future_website.delete');
        }

        if (PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.future_website.accept'))){
            $permissions[] = Config::get('common.permission.future_website.accept');
        }

        return $permissions;
    }

}
