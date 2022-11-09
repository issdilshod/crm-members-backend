<?php

namespace App\Services\VirtualOffice;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\VirtualOffice\VirtualOfficeResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\VirtualOffice\VirtualOffice;
use App\Services\Helper\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class VirtualOfficeService{

    private $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function all()
    {
        $virtualOffices = VirtualOffice::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->paginate(20);
        return VirtualOfficeResource::collection($virtualOffices);
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

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $virtualOffice['vo_provider_username'], Config::get('common.activity.virtual_office.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_add'),
            'status' => Config::get('common.status.actived')
        ]);

        return new VirtualOfficeResource($virtualOffice);
    }

    public function update(VirtualOffice $virtualOffice, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['approved'] = Config::get('common.status.actived');
        $virtualOffice->update($entity);

        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $virtualOffice['vo_provider_username'], Config::get('common.activity.virtual_office.updated')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_update'),
            'status' => Config::get('common.status.actived')
        ]);

        return new VirtualOfficeResource($virtualOffice);
    }

    public function delete(VirtualOffice $virtualOffice)
    {
        $virtualOffice->update(['status' => Config::get('common.status.deleted')]);
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $virtualOffice = VirtualOffice::create($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $virtualOffice['vo_provider_username'], Config::get('common.activity.virtual_office.pending')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $virtualOffice['vo_provider_username'] . "*", Config::get('common.activity.virtual_office.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return new VirtualOfficeResource($virtualOffice);
    }

    public function pending_update(VirtualOffice $virtualOffice, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['status'] = Config::get('common.status.pending');
        $virtualOffice->update($entity);

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $virtualOffice['vo_provider_username'], Config::get('common.activity.virtual_office.pending_update')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $virtualOffice['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $virtualOffice['vo_provider_username'] . "*", Config::get('common.activity.virtual_office.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return new VirtualOfficeResource($virtualOffice);
    }

    public function accept(VirtualOffice $virtualOffice, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $virtualOffice->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $virtualOffice['vo_provider_username'], Config::get('common.activity.virtual_office.accept')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $virtualOffice['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $virtualOffice['vo_provider_username'] . "*", Config::get('common.activity.virtual_office.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid']. ')'
        ]);

        return new VirtualOfficeResource($virtualOffice);
    }

    public function reject($uuid, $user_uuid)
    {
        $virtualOffice = VirtualOffice::where('uuid', $uuid)->first();

        $entity['status'] = Config::get('common.status.rejected');
        $virtualOffice->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $virtualOffice['vo_provider_username'], Config::get('common.activity.virtual_office.reject')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $virtualOffice['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $virtualOffice['vo_provider_username'] . "*", Config::get('common.activity.virtual_office.reject')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/vitual-offices/'.$virtualOffice['uuid']. ')'
        ]);

        return new VirtualOfficeResource($virtualOffice);
    }

    public function search($value)
    {
        $virtualOffice = VirtualOffice::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->where('vo_provider_username', $value)
                                            ->paginate(20);
        return  VirtualOfficeResource::collection($virtualOffice);
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

        if (isset($entity['vo_provider_username'])){
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

}