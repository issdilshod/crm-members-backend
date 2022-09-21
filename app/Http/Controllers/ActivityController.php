<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Models\API\Activity;
use App\Services\ActivityService;

class ActivityController extends Controller
{
    /**     @OA\GET(
      *         path="/api/activity",
      *         operationId="list_activity",
      *         tags={"Account"},
      *         summary="List of activity",
      *         description="List of activity",
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function index(ActivityService $activityService)
    {
        $activities = $activityService->getActivities();

        $activities = $activityService->setLink($activities);

        return ActivityResource::collection($activities);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function show(Activity $activity)
    {
        return new ActivityResource($activity);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function destroy(Activity $activity, ActivityService $activityService)
    {
        $activityService->deleteActivity($activity);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function by_user($uuid, ActivityService $activityService)
    {
        $activities = $activityService->getUserActivities($uuid);

        $activities = $activityService->setLink($activities);

        return ActivityResource::collection($activities);
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
      *             @OA\Response(
      *                 response=200,
      *                 description="Successfully",
      *                 @OA\JsonContent()
      *             ),
      *             @OA\Response(response=400, description="Bad request"),
      *             @OA\Response(response=401, description="Unauthenticated"),
      *             @OA\Response(response=404, description="Resource Not Found"),
      *     )
      */
    public function by_entity($uuid, ActivityService $activityService)
    {
        $activities = $activityService->getEntityActivities($uuid);

        $activities = $activityService->setLink($activities);

        return ActivityResource::collection($activities);
    }
}
