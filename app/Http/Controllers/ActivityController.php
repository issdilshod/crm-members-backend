<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Models\API\Activity;
use Illuminate\Http\Request;
use App\Helpers\UserSystemInfoHelper;

class ActivityController extends Controller
{
    /**     @OA\Get(
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
    public function index()
    {
        //
        $activity = Activity::where('status', '=', '1')->paginate(100);
        return ActivityResource::collection($activity);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
        /*$validated = $request->validate([
            'user_uuid' => 'required|string',
            'entity_uuid' => 'required|string',
            'status' => 'required|integer'
        ]);
        // Get device & IP
        // TODO: Change statistic description to dynamic
        $validated['description'] = 'static description';
        $validated['device'] = UserSystemInfoHelper::device_full();
        $validated['ip'] = UserSystemInfoHelper::ip();
        return new ActivityResource(Activity::create($validated));*/
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
        //
        return new ActivityResource($activity);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\API\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function edit(Activity $activity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\API\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Activity $activity)
    {
        //
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
    public function destroy(Activity $activity)
    {
        //
        $activity->update(['status' => '0']);
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
    public function by_user($uuid)
    {
        $activity = Activity::where('status', '=', '1')->where('user_uuid', '=', $uuid)->paginate(100);
        return ActivityResource::collection($activity);
        //
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
    public function by_entity($uuid)
    {
        $activity = Activity::where('status', '=', '1')->where('entity_uuid', '=', $uuid)->paginate(100);
        return ActivityResource::collection($activity);
        //
    }
}
