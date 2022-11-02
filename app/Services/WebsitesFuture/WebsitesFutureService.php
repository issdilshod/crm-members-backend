<?php

namespace App\Services\WebsitesFuture;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\WebsitesFuture\WebsitesFutureResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\WebsitesFuture\WebsitesFuture;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class WebsitesFutureService{

    public function all()
    {
        $futureWebsites = WebsitesFuture::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->paginate(20);
        return WebsitesFutureResource::collection($futureWebsites);
    }

    public function one(WebsitesFuture $websitesFuture)
    {
        $websitesFuture = new WebsitesFutureResource($websitesFuture);
        return $websitesFuture;
    }

    public function create($entity)
    {
        $futureWebsites = WebsitesFuture::create($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $futureWebsites['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $futureWebsites['link'], Config::get('common.activity.websites_future.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.websites_future_add'),
            'status' => Config::get('common.status.actived')
        ]);

        return new WebsitesFutureResource($futureWebsites);
    }

    public function update(WebsitesFuture $websitesFuture, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $websitesFuture->update($entity);

        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $websitesFuture['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $websitesFuture['link'], Config::get('common.activity.websites_future.updated')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.websites_future_update'),
            'status' => Config::get('common.status.actived')
        ]);

        return new WebsitesFutureResource($websitesFuture);
    }

    public function delete(WebsitesFuture $websitesFuture)
    {
        $websitesFuture->update(['status' => Config::get('common.status.deleted')]);
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $futureWebsites = WebsitesFuture::create($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $futureWebsites['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $futureWebsites['link'], Config::get('common.activity.websites_future.pending')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.websites_future_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        return new WebsitesFutureResource($futureWebsites);
    }

    public function pending_update($uuid, $entity)
    {
        $entity['updated_at'] = Carbon::now();
        $websitesFuture = WebsitesFuture::where('uuid', $uuid)
                                    ->first();

        $entity['status'] = Config::get('common.status.pending');
        $websitesFuture->update($entity);

        // logs
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $websitesFuture['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $websitesFuture['link'], Config::get('common.activity.websites_future.pending_update')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.websites_future_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{link}", "*" . $websitesFuture['link'] . "*", Config::get('common.activity.websites_future.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/future_websites/'.$websitesFuture['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return new WebsitesFutureResource($websitesFuture);
    }

    public function accept(WebsitesFuture $websitesFuture, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $websitesFuture->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $websitesFuture['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $websitesFuture['link'], Config::get('common.activity.websites_future.accept')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.websites_future_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $websitesFuture['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{link}", "*" . $websitesFuture['link'] . "*", Config::get('common.activity.websites_future.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/future_websites/'.$websitesFuture['uuid']. ')'
        ]);

        return new WebsitesFutureResource($websitesFuture);
    }

    public function reject($uuid, $user_uuid)
    {
        $websitesFuture = WebsitesFuture::where('uuid', $uuid)->first();

        $entity['status'] = Config::get('common.status.rejected');
        $websitesFuture->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $websitesFuture['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{link}", $websitesFuture['link'], Config::get('common.activity.websites_future.reject')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.websites_future_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $websitesFuture['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{link}", "*" . $websitesFuture['link'] . "*", Config::get('common.activity.websites_future.reject')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/future_websites/'.$websitesFuture['uuid']. ')'
        ]);

        return new WebsitesFutureResource($websitesFuture);
    }

    public function search($value)
    {
        $futureWebsites = WebsitesFuture::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->where('link', $value)
                                            ->paginate(20);
        return WebsitesFutureResource::collection($futureWebsites);
    }

    public function check($entity)
    {
        $check = [];

        if (isset($entity['link'])){
            $check['tmp'] = WebsitesFuture::select('link')
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
            $check['tmp'] = WebsitesFuture::select('link')
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