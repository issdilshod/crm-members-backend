<?php

namespace App\Services\VirtualOffice;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\VirtualOffice\VirtualOfficePendingResource;
use App\Http\Resources\VirtualOffice\VirtualOfficeResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\VirtualOffice\VirtualOffice;
use App\Services\Account\ActivityService;
use App\Services\Company\CompanyService;
use App\Services\Helper\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class VirtualOfficeService{

    private $notificationService;
    private $activityService;
    private $companyService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->activityService = new ActivityService();
        $this->companyService = new CompanyService();
    }

    public function all()
    {
        $virtualOffices = VirtualOffice::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->paginate(20);

        foreach($virtualOffices AS $key => $value):
            $virtualOffices[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return VirtualOfficePendingResource::collection($virtualOffices);
    }

    public function for_pending($user_uuid)
    {
        $virtualOffices = VirtualOffice::orderBy('updated_at', 'DESC')
                                        ->where('status', '!=', Config::get('common.status.deleted'))
                                        ->when(($user_uuid!=''), function ($q) use($user_uuid){
                                            return $q->where('user_uuid', $user_uuid);
                                        })
                                        ->paginate(5);

        foreach($virtualOffices AS $key => $value):
            $virtualOffices[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return VirtualOfficePendingResource::collection($virtualOffices);
    }

    public function one(VirtualOffice $virtualOffice)
    {
        $virtualOffice = new VirtualOfficeResource($virtualOffice);
        return $virtualOffice;
    }

    public function create($entity)
    {
        $entity['approved'] = Config::get('common.status.actived');
        $virtualOffice = VirtualOffice::create($entity);

        // get name
        $name = $this->get_name($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.virtual_office.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_add'),
            'status' => Config::get('common.status.actived')
        ]);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function update(VirtualOffice $virtualOffice, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['approved'] = Config::get('common.status.actived');
        $virtualOffice->update($entity);

        // get name
        $name = $this->get_name($entity);

        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.virtual_office.updated')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_update'),
            'status' => Config::get('common.status.actived')
        ]);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function delete(VirtualOffice $virtualOffice)
    {
        $virtualOffice->update(['status' => Config::get('common.status.deleted')]);
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $virtualOffice = VirtualOffice::create($entity);

        // get name
        $name = $this->get_name($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.virtual_office.pending')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $name . "*", Config::get('common.activity.virtual_office.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function pending_update(VirtualOffice $virtualOffice, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['status'] = Config::get('common.status.pending');
        $virtualOffice->update($entity);

        // get name
        $name = $this->get_name($entity);

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.virtual_office.pending_update')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $virtualOffice['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $name . "*", Config::get('common.activity.virtual_office.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function accept(VirtualOffice $virtualOffice, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $virtualOffice->update($entity);

        // get name
        $name = $this->get_name($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.virtual_office.accept')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $virtualOffice['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $name . "*", Config::get('common.activity.virtual_office.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid']. ')'
        ]);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function reject($uuid, $user_uuid)
    {
        $virtualOffice = VirtualOffice::where('uuid', $uuid)->first();

        $entity['status'] = Config::get('common.status.rejected');
        $virtualOffice->update($entity);

        // get name
        $name = $this->get_name($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.virtual_office.reject')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $virtualOffice['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $name . "*", Config::get('common.activity.virtual_office.reject')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/vitual-offices/'.$virtualOffice['uuid']. ')'
        ]);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function search($value)
    {
        $virtualOffices = VirtualOffice::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->where('vo_provider_name', $value)
                                            ->paginate(20);

        foreach($virtualOffices AS $key => $value):
            $virtualOffices[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return VirtualOfficePendingResource::collection($virtualOffices);
    }

    public function check($entity)
    {
        $check = [];

        if (isset($entity['address_line2'])){
            $check['tmp'] = VirtualOffice::select('address_line1', 'address_line2', 'city', 'state', 'postal')
                                        ->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->where('address_line1', $entity['address_line1'])
                                        ->where('address_line2', $entity['address_line2'])
                                        ->where('city', $entity['city'])
                                        ->where('state', $entity['state'])
                                        ->where('postal', $entity['postal'])
                                        ->first();
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

        if (isset($entity['address_line2'])){
            $check['tmp'] = VirtualOffice::select('address_line1', 'address_line2', 'city', 'state', 'postal')
                                        ->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->where('uuid', '!=', $ignore_uuid)
                                        ->where('address_line1', $entity['address_line1'])
                                        ->where('address_line2', $entity['address_line2'])
                                        ->where('city', $entity['city'])
                                        ->where('state', $entity['state'])
                                        ->where('postal', $entity['postal'])
                                        ->first();
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

    private function get_name($entity)
    {
        $name = $entity['vo_provider_name'];
        if ($entity['vo_signer_uuid']!=''){
            $company = $this->companyService->by_director($entity['vo_signer_uuid']);
            if ($company!=null){
                $name = ' for company ' . $company['legal_name'];
            }
        }
        return $name;
    }

}