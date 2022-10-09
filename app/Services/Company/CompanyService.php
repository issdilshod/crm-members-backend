<?php

namespace App\Services\Company;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Company\CompanyResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Company\Company;
use Illuminate\Support\Facades\Config;

class CompanyService {


    public function __construct()
    {
        //
    }

    public function all()
    {
        $companies = Company::orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(20);
        return CompanyResource::collection($companies);
    }

    public function one(Company $company)
    {
        $company = new CompanyResource($company);
        return $company;
    }

    public function delete(Company $company)
    {
        $company->update(['status' => Config::get('common.status.deleted')]);
    }

    public function search($value)
    {
        $companies = Company::orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('legal_name', 'like', '%'.$value.'%')
                                ->paginate(20);
        return CompanyResource::collection($companies);
    }

    public function check($entity)
    {
        $check = [];

        $check['tmp'] = Company::select('legal_name')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('legal_name', $entity['legal_name'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Director
        $check['tmp'] = Company::select('director_uuid')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('director_uuid', $entity['director_uuid'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // EIN
        $check['tmp'] = Company::select('ein')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('ein', $entity['ein'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Business number
        $check['tmp'] = Company::select('business_number')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('business_number', $entity['business_number'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Voip Login
        $check['tmp'] = Company::select('voip_login')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('voip_login', $entity['voip_login'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Business mobile number login
        $check['tmp'] = Company::select('business_mobile_number_login')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('business_mobile_number_login', $entity['business_mobile_number_login'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Website
        $check['tmp'] = Company::select('website')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('website', $entity['website'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['website_c'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Db report number
        $check['tmp'] = Company::select('db_report_number')
                                ->where('status', Config::get('common.status.actived'))
                                ->where('db_report_number', $entity['db_report_number'])->first();
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

        $check['tmp'] = Company::select('legal_name')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('legal_name', $entity['legal_name'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Director
        $check['tmp'] = Company::select('director_uuid')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('director_uuid', $entity['director_uuid'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // EIN
        $check['tmp'] = Company::select('ein')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('ein', $entity['ein'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Business number
        $check['tmp'] = Company::select('business_number')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('business_number', $entity['business_number'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Voip Login
        $check['tmp'] = Company::select('voip_login')
                                ->where('uuid', '!=', $ignore_uuid)            
                                ->where('status', Config::get('common.status.actived'))
                                ->where('voip_login', $entity['voip_login'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Business mobile number login
        $check['tmp'] = Company::select('business_mobile_number_login')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('business_mobile_number_login', $entity['business_mobile_number_login'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Website
        $check['tmp'] = Company::select('website')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('website', $entity['website'])->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['website_c'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // Db report number
        $check['tmp'] = Company::select('db_report_number')
                                ->where('uuid', '!=', $ignore_uuid)
                                ->where('status', Config::get('common.status.actived'))
                                ->where('db_report_number', $entity['db_report_number'])->first();
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
        $company = Company::create($entity);
        
        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.company.add'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_add'),
            'status' => Config::get('common.status.actived')
        ]);

        return $company;
    }

    public function update(Company $company, $entity)
    {
        $company->update($entity);

        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.company.updated'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_update'),
            'status' => Config::get('common.status.actived')
        ]);

        return $company;
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $company = Company::create($entity);

        // logs
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.company.pending'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                Config::get('common.activity.company.pending') . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return $company;
    }

    public function pending_update($uuid, $entity)
    {
        $company = Company::where('uuid', $uuid)
                            ->where('status', Config::get('common.status.pending'))
                            ->first();

        $entity['status'] = Config::get('common.status.pending');
        $company->update($entity);

        // logs
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.company.pending_update'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                Config::get('common.activity.company.pending_update') . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return $company;
    }

    public function accept(Company $company, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $company->update($entity);

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.company.accept'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $company['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => Config::get('common.activity.company.accept') . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid']. ')'
        ]);

        return $company;
    }

    public function reject($uuid, $user_uuid)
    {
        $company = Company::where('uuid', $uuid)->first();
        $company->update(['status' => Config::get('common.status.rejected')]);

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.company.reject'),
            'changes' => '',
            'action_code' => Config::get('common.activity.codes.company_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $company['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => Config::get('common.activity.company.reject') . "\n" .
                        '[link to change](' .env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid']. ')'
        ]);
    }

}