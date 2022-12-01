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

    public function by_entity_last($uuid)
    {
        $activity = Activity::orderBy('updated_at', 'DESC')
                                ->where('entity_uuid', $uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->first();
        $activity = $this->setLink($activity);
        return new ActivityResource($activity);
    }

    public function one(Activity $activity)
    {
        $activity = new ActivityResource($activity);
        return $activity;
    }

    public function delete(Activity $activity)
    {
        $activity->update(['status' => Config::get('common.status.deleted')]);
    }

    public function setLink($activity)
    {
        $link = '';
        if ($activity['action_code']!=0){
            $link = '/' . Config::get('common.activity.codes_link.'.$activity['action_code']) . '/' . $activity['entity_uuid'];
        }
        $activity['link'] = $link;
        return $activity;
    }

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