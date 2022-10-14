<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account\Activity;
use App\Policies\PermissionPolicy;
use App\Services\Account\ActivityService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{

    private $activityService;

    public function __construct()
    {
        $this->activityService = new ActivityService();
    }

    /**     @OA\GET(
      *         path="/api/activity",
      *         operationId="list_activity",
      *         tags={"Account"},
      *         summary="List of activity",
      *         description="List of activity",
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(Request $request)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return $this->activityService->by_user($request->user_uuid);
        }

        $activities = $this->activityService->all();
        
        return $activities;
    }

    /**     @OA\GET(
      *         path="/api/activity/{uuid}",
      *         operationId="get_activity",
      *         tags={"Account"},
      *         summary="Get activity",
      *         description="Get activity",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="activity uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Activity $activity)
    {
        $activity = $this->activityService->one($activity);
        return $activity;
    }

    /**     @OA\DELETE(
      *         path="/api/activity/{uuid}",
      *         operationId="delete_activity",
      *         tags={"Account"},
      *         summary="Delete activity",
      *         description="Delete activity",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="activity uuid",
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
    public function destroy(Request $request, Activity $activity)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $this->activityService->delete($activity);
    }

    /**     @OA\GET(
      *         path="/api/activity/user/{uuid}",
      *         operationId="list_user_activity",
      *         tags={"Account"},
      *         summary="List of user activity",
      *         description="List of user activity",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="user uuid",
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
    public function by_user(Request $request, $uuid)
    {
        // permission
        if (!PermissionPolicy::permission($request->user_uuid)){
            return response()->json([ 'data' => 'Not Authorized' ], 403);
        }

        $activities = $this->activityService->by_user($uuid);
        return $activities;
    }

    /**     @OA\GET(
      *         path="/api/activity/entity/{uuid}",
      *         operationId="list_entity_activity",
      *         tags={"Account"},
      *         summary="List of entity activity",
      *         description="List of entity activity",
      *             @OA\Parameter(
      *                 name="uuid",
      *                 in="path",
      *                 description="entity uuid",
      *                 @OA\Schema(
      *                     type="string",
      *                     format="uuid"
      *                 ),
      *                 required=true
      *             ),
      *             @OA\Response(response=200, description="Successfully"),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Not Authenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function by_entity($uuid)
    {
        $activities = $this->activityService->by_entity($uuid);
        return $activities;
    }
}
