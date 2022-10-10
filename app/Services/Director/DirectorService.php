<?php

namespace App\Services\Director;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Director\DirectorPendingResource;
use App\Http\Resources\Director\DirectorResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Director\Director;
use App\Services\Account\ActivityService;
use App\Services\Helper\AddressService;
use App\Services\Helper\EmailService;
use App\Services\Helper\NotificationService;
use Illuminate\Support\Facades\Config;

class DirectorService {

    private $addressService;
    private $emailService;
    private $notificationService;
    private $activityService;

    public function __construct()
    {
        $this->addressService = new AddressService();
        $this->emailService = new EmailService();
        $this->notificationService = new NotificationService();
        $this->activityService = new ActivityService();
    }

    public function all()
    {
        $directors = Director::orderBy('created_at', 'DESC')
                            ->where('status', Config::get('common.status.actived'))
                            ->paginate(20);
        return DirectorResource::collection($directors);
    }

    public function by_user($user_uuid)
    {
        $directors = Director::orderBy('updated_at', 'DESC')
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->where('user_uuid', $user_uuid)
                                ->paginate(20);

        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return DirectorPendingResource::collection($directors);
    }

    public function headquarters()
    {
        $directors = Director::orderBy('updated_at', 'DESC')
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->paginate(20);

        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return DirectorPendingResource::collection($directors);
    }

    public function one(Director $director)
    {
        $director = new DirectorResource($director);
        return $director;
    }

    public function delete(Director $director)
    {
        $director->update(['status' => Config::get('common.status.deleted')]);
        $this->addressService->delete_by_entity($director->uuid);
        $this->emailService->delete_by_entity($director->uuid);
    }

    public function search($value)
    {
        $directors = Director::orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->whereRaw("concat(first_name, ' ', last_name) like '%".$value."%'")
                                ->paginate(20);
        return DirectorResource::collection($directors);
    }

    public function check($entity)
    {

        $check = [];

        // Names
        /*$check['tmp'] = Director::select('first_name', 'middle_name', 'last_name')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('first_name', $entity['first_name'])
                                    ->where('middle_name', (isset($entity['middle_name'])?$entity['middle_name']:''))
                                    ->where('last_name', $entity['last_name'])
                                    ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);*/

        // Ssn
        $check['tmp'] = Director::select('ssn_cpn')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('ssn_cpn', $entity['ssn_cpn'])
                                    ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Company 
        $check['tmp'] = Director::select('company_association')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('company_association', $entity['company_association'])
                                    ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Phone
        $check['tmp'] = Director::select('phone_number')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('phone_number', $entity['phone_number'])
                                    ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        return $check;
    }

    public function check_ignore($entity, $ignore_uuid)
    {

        $check = [];

        // Names
        /*$check['tmp'] = Director::select('first_name', 'middle_name', 'last_name')
                                    ->where('uuid', '!=', $ignore_uuid)
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('first_name', $entity['first_name'])
                                    ->where('middle_name', (isset($entity['middle_name'])?$entity['middle_name']:''))
                                    ->where('last_name', $entity['last_name'])
                                    ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);*/

        // Ssn
        $check['tmp'] = Director::select('ssn_cpn')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('ssn_cpn', $entity['ssn_cpn'])
                                ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Company 
        $check['tmp'] = Director::select('company_association')
                                    ->where('uuid', '!=', $ignore_uuid)
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('company_association', $entity['company_association'])
                                    ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Phone
        $check['tmp'] = Director::select('phone_number')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('phone_number', $entity['phone_number'])
                                ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        return $check;
    }

    public function create($entity)
    {
        $director = Director::create($entity);

        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.add'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_add'),
            'status' => Config::get('common.status.actived')
        ]);

        return $director;
    }

    public function update(Director $director, $entity)
    {
        $director->update($entity);

        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.update'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_update'),
            'status' => Config::get('common.status.actived')
        ]);

        return $director;
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $director = Director::create($entity);

        // logs
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.pending'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                Config::get('common.activity.director.pending') . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return $director;
    }

    public function pending_update($uuid, $entity)
    {
        $director = Director::where('uuid', $uuid)
                                ->where('status', Config::get('common.status.pending'))
                                ->first();

        $entity['status'] = Config::get('common.status.pending');
        $director->update($entity);

        // logs
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.pending_update'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                Config::get('common.activity.director.pending_update') . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return $director;
    }

    public function accept(Director $director, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $director->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.accept'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $director['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => Config::get('common.activity.director.accept') . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid']. ')'
        ]);

        return $director;
    }

    public function reject($uuid, $user_uuid)
    {
        $director = Director::where('uuid', $uuid)->first();
        $director->update(['status' => Config::get('common.status.rejected')]);

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.reject'),
            'changes' => '',
            'action_code' => Config::get('common.activity.codes.director_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $director['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => Config::get('common.activity.director.reject') . "\n" .
                        '[link to change](' .env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid']. ')'
        ]);

    }

}