<?php

namespace App\Services\Company;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Company\FutureCompanyResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Company\FutureCompany;
use App\Services\Helper\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class FutureCompanyService{

    private $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function all()
    {
        $futureCompanies = FutureCompany::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->paginate(20);
        return FutureCompanyResource::collection($futureCompanies);
    }

    public function one(FutureCompany $futureCompany)
    {
        $futureCompany = new FutureCompanyResource($futureCompany);
        return $futureCompany;
    }

    public function create($entity)
    {
        $futureCompany = FutureCompany::create($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $futureCompany['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $futureCompany['revival_date'], Config::get('common.activity.future_company.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_company_add'),
            'status' => Config::get('common.status.actived')
        ]);

        return new FutureCompanyResource($futureCompany);
    }

    public function update(FutureCompany $futureCompany, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $futureCompany->update($entity);

        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $futureCompany['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $futureCompany['revival_date'], Config::get('common.activity.future_company.updated')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_company_update'),
            'status' => Config::get('common.status.actived')
        ]);

        return new FutureCompanyResource($futureCompany);
    }

    public function delete(FutureCompany $futureCompany)
    {
        $futureCompany->update(['status' => Config::get('common.status.deleted')]);
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $futureCompany = FutureCompany::create($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $futureCompany['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $futureCompany['revival_date'], Config::get('common.activity.future_company.pending')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_company_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $futureCompany['revival_date'] . "*", Config::get('common.activity.future_company.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/future-companies/'.$futureCompany['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return new FutureCompanyResource($futureCompany);
    }

    public function pending_update(FutureCompany $futureCompany, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['status'] = Config::get('common.status.pending');
        $futureCompany->update($entity);

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $futureCompany['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $futureCompany['revival_date'], Config::get('common.activity.future_company.pending_update')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_company_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $futureCompany['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $futureCompany['revival_date'] . "*", Config::get('common.activity.future_company.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/future-companies/'.$futureCompany['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return new FutureCompanyResource($futureCompany);
    }

    public function accept(FutureCompany $futureCompany, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $futureCompany->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $futureCompany['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $futureCompany['revival_date'], Config::get('common.activity.future_company.accept')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_company_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $futureCompany['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $futureCompany['revival_date'] . "*", Config::get('common.activity.future_company.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/future-company/'.$futureCompany['uuid']. ')'
        ]);

        return new FutureCompanyResource($futureCompany);
    }

    public function reject($uuid, $user_uuid)
    {
        $futureCompany = FutureCompany::where('uuid', $uuid)->first();

        $entity['status'] = Config::get('common.status.rejected');
        $futureCompany->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $futureCompany['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $futureCompany['revival_date'], Config::get('common.activity.future_company.reject')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.future_company_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $futureCompany['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $futureCompany['revival_date'] . "*", Config::get('common.activity.future_company.reject')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/future-companies/'.$futureCompany['uuid']. ')'
        ]);

        return new FutureCompanyResource($futureCompany);
    }

    public function search($value)
    {
        $futureCompany = FutureCompany::orderBy('updated_at')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->where('revival_date', $value)
                                            ->paginate(20);
        return FutureCompanyResource::collection($futureCompany);
    }

    public function check($entity)
    {
        $check = [];

        if (isset($entity['revival_date']) && isset($entity['virtual_office_uuid'])){
            $check['tmp'] = FutureCompany::select('revival_date', 'virtual_office_uuid')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('revival_date', $entity['revival_date'])
                                        ->where('virtual_office_uuid', $entity['virtual_office_uuid'])
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

        if (isset($entity['revival_date']) && isset($entity['virtual_office_uuid'])){
            $check['tmp'] = FutureCompany::select('revival_date', 'virtual_office_uuid')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('revival_date', $entity['revival_date'])
                                        ->where('virtual_office_uuid', $entity['virtual_office_uuid'])
                                        ->where('uuid', '!=', $ignore_uuid)
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