<?php

namespace App\Http\Controllers\WebsitesFuture;

use App\Http\Controllers\Controller;
use App\Models\WebsitesFuture\WebsitesFuture;
use App\Policies\PermissionPolicy;
use App\Services\WebsitesFuture\WebsitesFutureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class WebsitesFutureController extends Controller
{
    private $websitesFutureService;

    public function __construct()
    {
        $this->websitesFutureService = new WebsitesFutureService();
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
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.view'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $websitesFuture = $this->websitesFutureService->all();

        return $websitesFuture;
    }

    /**     @OA\POST(
      *         path="/api/futture-websites",
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
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.store'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required',
            'user_uuid' => ''
        ]);

        $check = [];

        $tmpCheck = $this->websitesFutureService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $websitesFuture = $this->websitesFutureService->create($validated);

        return $websitesFuture;
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
    public function show(Request $request, WebsitesFuture $websitesFuture)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.view'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $websitesFuture = $this->websitesFutureService->one($websitesFuture);

        return $websitesFuture;
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
    public function update(Request $request, WebsitesFuture $websitesFuture)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.update'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required'
        ]);

        $check = [];

        $tmpCheck = $this->websitesFutureService->check_ignore($validated, $websitesFuture->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $websitesFuture = $this->websitesFutureService->update($websitesFuture, $validated, $request->user_uuid);

        return $websitesFuture;
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
    public function destroy(Request $request, WebsitesFuture $websitesFuture)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.delete'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $this->websitesFutureService->delete($websitesFuture);
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
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.save'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required'
        ]);

        $check = [];

        $tmpCheck = $this->websitesFutureService->check($validated);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $websitesFuture = $this->websitesFutureService->pending($validated);

        return $websitesFuture;
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
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.save'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required'
        ]);

        $check = [];

        $websitesFuture = WebsitesFuture::where('uuid', $uuid)->first();

        $tmpCheck = $this->websitesFutureService->check_ignore($validated, $websitesFuture->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $websitesFuture = $this->websitesFutureService->pending_update($websitesFuture, $validated, $request->user_uuid);

        return $websitesFuture;
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
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.accept'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $validated = $request->validate([
            'sic_code_uuid' => 'required',
            'link' => 'required'
        ]);

        $check = [];

        $websitesFuture = WebsitesFuture::where('uuid', $uuid)->first();

        $tmpCheck = $this->websitesFutureService->check_ignore($validated, $websitesFuture->uuid);
        $check = array_merge($check, $tmpCheck);

        // exists
        if (count($check)>0){
            return response()->json([
                'data' => $check,
            ], 409);
        }

        $websitesFuture = $this->websitesFutureService->accept($websitesFuture, $validated, $request->user_uuid);

        return $websitesFuture;
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
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.reject'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $this->websitesFutureService->reject($uuid, $request->user_uuid);
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
        if (!PermissionPolicy::permission($request->user_uuid, Config::get('common.permission.websites_future.view'))){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $websitesFuture = $this->websitesFutureService->search($search);

        return $websitesFuture;
    }

}