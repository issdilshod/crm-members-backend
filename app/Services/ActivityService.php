<?php

namespace App\Services;

use App\Models\API\Activity;
use Illuminate\Support\Facades\Config;

class ActivityService {

    /**
     * Return 10 last activities
     *
     * @return Activity
     */
    public function getActivities()
    {
        $activities = Activity::orderBy('updated_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(10);
        return $activities;            
    }

    /**
     * Return 10 last activities of user
     *
     * @return Activity
     */
    public function getUserActivities($uuid)
    {
        $activities = Activity::orderBy('updated_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('user_uuid', $uuid)
                                ->paginate(10);
        return $activities;
    }

    /**
     * Return 10 last activities of entity
     *
     * @return Activity
     */
    public function getEntityActivities($uuid)
    {
        $activities = Activity::orderBy('updated_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('entity_uuid', $uuid)
                                ->paginate(10);
        return $activities;
    }

    /**
     * Delete activity by id
     *
     * @return void
     */
    public function deleteActivity(Activity $activity)
    {
        $activity->update(['status' => Config::get('common.status.deleted')]);
    }

    /**
     * Return Activity with links
     *
     * @return Activity
     */
    public function setLink($activities)
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