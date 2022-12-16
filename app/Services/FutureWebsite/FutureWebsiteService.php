<?php

namespace App\Services\FutureWebsite;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\FutureWebsite\FutureWebsitePendingResource;
use App\Http\Resources\FutureWebsite\FutureWebsiteResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\FutureWebsite\FutureWebsite;
use App\Services\Account\ActivityService;
use App\Services\Helper\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class FutureWebsiteService{

    private $notificationService;
    private $activityService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->activityService = new ActivityService();
    }

    public function all()
    {
        $futureWebsites = FutureWebsite::orderBy('updated_at')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->paginate(20);
                                        
        foreach($futureWebsites AS $key => $value):
            $futureWebsites[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return FutureWebsitePendingResource::collection($futureWebsites);
    }

    public function one(FutureWebsite $futureWebsite)
    {
        $futureWebsite = new FutureWebsiteResource($futureWebsite);
        return $futureWebsite;
    }

    public function create($entity)
    {
        $entity['approved'] = Config::get('common.status.actived');
        $futureWebsite = FutureWebsite::create($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $futureWebsite['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $futureWebsite['link'], Config::get('common.activity.future_website.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_website_add'),
            'status' => Config::get('common.status.actived')
        ]);

        $futureWebsite['last_activity'] = $this->activityService->by_entity_last($futureWebsite['uuid']);
        return new FutureWebsitePendingResource($futureWebsite);
    }

    public function update(FutureWebsite $futureWebsite, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['approved'] = Config::get('common.status.actived');
        $futureWebsite->update($entity);

        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $futureWebsite['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $futureWebsite['link'], Config::get('common.activity.future_website.updated')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_website_update'),
            'status' => Config::get('common.status.actived')
        ]);

        $futureWebsite['last_activity'] = $this->activityService->by_entity_last($futureWebsite['uuid']);
        return new FutureWebsitePendingResource($futureWebsite);
    }

    public function delete(FutureWebsite $futureWebsite)
    {
        $futureWebsite->update(['status' => Config::get('common.status.deleted')]);
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $futureWebsite = FutureWebsite::create($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $futureWebsite['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $futureWebsite['link'], Config::get('common.activity.future_website.pending')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_website_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{link}", "*" . $futureWebsite['link'] . "*", Config::get('common.activity.future_website.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/future-websites/'.$futureWebsite['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        $futureWebsite['last_activity'] = $this->activityService->by_entity_last($futureWebsite['uuid']);
        return new FutureWebsitePendingResource($futureWebsite);
    }

    public function pending_update(FutureWebsite $futureWebsite, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['status'] = Config::get('common.status.pending');
        $futureWebsite->update($entity);

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $futureWebsite['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $futureWebsite['link'], Config::get('common.activity.future_website.pending_update')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_website_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $futureWebsite['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{link}", "*" . $futureWebsite['link'] . "*", Config::get('common.activity.future_website.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/future-websites/'.$futureWebsite['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        $futureWebsite['last_activity'] = $this->activityService->by_entity_last($futureWebsite['uuid']);
        return new FutureWebsitePendingResource($futureWebsite);
    }

    public function accept(FutureWebsite $futureWebsite, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $futureWebsite->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $futureWebsite['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $futureWebsite['link'], Config::get('common.activity.future_website.accept')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_website_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $futureWebsite['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{link}", "*" . $futureWebsite['link'] . "*", Config::get('common.activity.future_website.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/future-websites/'.$futureWebsite['uuid']. ')'
        ]);

        $futureWebsite['last_activity'] = $this->activityService->by_entity_last($futureWebsite['uuid']);
        return new FutureWebsitePendingResource($futureWebsite);
    }

    public function reject($uuid, $user_uuid)
    {
        $futureWebsite = FutureWebsite::where('uuid', $uuid)->first();

        $entity['status'] = Config::get('common.status.rejected');
        $futureWebsite->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $futureWebsite['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $futureWebsite['link'], Config::get('common.activity.future_website.reject')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_website_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $futureWebsite['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{link}", "*" . $futureWebsite['link'] . "*", Config::get('common.activity.future_website.reject')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/future-websites/'.$futureWebsite['uuid']. ')'
        ]);

        $futureWebsite['last_activity'] = $this->activityService->by_entity_last($futureWebsite['uuid']);
        return new FutureWebsitePendingResource($futureWebsite);
    }

    public function search($value)
    {
        $futureWebsites = FutureWebsite::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->where('link', $value)
                                            ->paginate(20);
        
        foreach($futureWebsites AS $key => $value):
            $futureWebsites[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return FutureWebsitePendingResource::collection($futureWebsites);
    }

    public function check($entity)
    {
        $check = [];

        if (isset($entity['link'])){
            $check['tmp'] = FutureWebsite::select('link')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('link', $entity['link'])->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check[$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        return $check;
    }

    public function check_ignore($entity, $ignore_uuid)
    {
        $check = [];

        if (isset($entity['link'])){
            $check['tmp'] = FutureWebsite::select('link')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('uuid', '!=', $ignore_uuid)
                                        ->where('link', $entity['link'])->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check[$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        return $check;
    }

}