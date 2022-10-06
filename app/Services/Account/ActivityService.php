<?php

namespace App\Services\Account;

use App\Http\Resources\Account\ActivityResource;
use App\Models\Account\Activity;
use Illuminate\Support\Facades\Config;

class ActivityService {

    public function all()
    {
        $activities = Activity::orderBy('updated_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(10);
        $activities = $this->setLinks($activities);
        return ActivityResource::collection($activities);         
    }

    public function by_user($uuid)
    {
        $activities = Activity::orderBy('updated_at', 'DESC')
                                ->where('user_uuid', $uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(10);
        $activities = $this->setLinks($activities);
        return ActivityResource::collection($activities);         
    }

    public function by_entity($uuid)
    {
        $activities = Activity::orderBy('updated_at', 'DESC')
                                ->where('entity_uuid', $uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(10);
        $activities = $this->setLinks($activities);
        return ActivityResource::collection($activities);         
    }

    public function one(Activity $activity)
    {
        $activity = new ActivityResource($activity);
        return $activity;
    }

    /**
     * Delete activity by id
     *
     * @return void
     */
    public function delete(Activity $activity)
    {
        $activity->update(['status' => Config::get('common.status.deleted')]);
    }

    /**
     * Set mapping links to activity
     * 
     * @return Array(Activity)
     */
    private function setLinks($activities)
    {
        foreach ($activities AS $key => $value):
            $link = '';
            if ($value['action_code']!=0){
                $link = '/' . Config::get('common.activity.codes_link.'.$value['action_code']) . '/' . $value['entity_uuid'];
            }
            $activities[$key]['link'] = $link;
        endforeach;
        return $activities;
    }
}