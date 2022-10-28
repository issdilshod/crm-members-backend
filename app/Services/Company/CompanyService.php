<?php

namespace App\Services\Company;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Company\CompanyPendingResource;
use App\Http\Resources\Company\CompanyResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Company\Company;
use App\Services\Account\ActivityService;
use App\Services\Helper\AddressService;
use App\Services\Helper\BankAccountService;
use App\Services\Helper\EmailService;
use App\Services\Helper\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class CompanyService {

    private $addressService;
    private $emailService;
    private $bankAccountService;
    private $notificationService;
    private $activityService;

    public function __construct()
    {
        $this->addressService = new AddressService();
        $this->emailService = new EmailService();
        $this->notificationService = new NotificationService();
        $this->bankAccountService = new BankAccountService();
        $this->activityService = new ActivityService();
    }

    public function all()
    {
        $companies = Company::orderBy('legal_name', 'ASC')
                                ->orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(20);
        return CompanyResource::collection($companies);
    }

    public function by_user($user_uuid)
    {
        $companies = Company::orderBy('updated_at', 'DESC')
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->where('user_uuid', $user_uuid)
                                ->paginate(10);

        foreach($companies AS $key => $value):
            $companies[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return CompanyPendingResource::collection($companies);
    }

    public function by_user_search($user_uuid, $search)
    {
        $companies = Company::select('companies.*')
                                ->orderBy('companies.updated_at', 'DESC')
                                ->groupBy('companies.uuid')
                                ->join('addresses', 'addresses.entity_uuid', '=', 'companies.uuid')
                                ->join('emails', 'emails.entity_uuid', '=', 'companies.uuid')
                                ->join('bank_accounts', 'bank_accounts.entity_uuid', '=', 'companies.uuid')
                                ->where('companies.status', '!=', Config::get('common.status.deleted'))
                                ->where('companies.user_uuid', $user_uuid)
                                ->where(function ($q) use($search) {
                                    $q->where('companies.legal_name', 'like', '%'.$search.'%')
                                        ->orWhere('companies.ein', 'like', $search.'%')
                                        ->orWhere('companies.business_number', 'like', $search.'%')
                                        ->orWhere('companies.voip_login', 'like', $search.'%')
                                        ->orWhere('companies.business_mobile_number', 'like', $search.'%')
                                        ->orWhere('companies.business_mobile_number_login', 'like', $search.'%')
                                        ->orWhere('companies.website', 'like', $search.'%')
                                        ->orWhere('companies.db_report_number', 'like', $search.'%')
                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.city, ' ', addresses.state) like '%".$search."%'")
                                        ->orWhere('emails.email', 'like', $search.'%')
                                        ->orWhere('emails.phone', 'like', $search.'%')
                                        ->orWhere('bank_accounts.name', 'like', $search.'%')
                                        ->orWhere('bank_accounts.website', 'like', $search.'%')
                                        ->orWhere('bank_accounts.username', 'like', $search.'%')
                                        ->orWhere('bank_accounts.account_number', 'like', $search.'%')
                                        ->orWhere('bank_accounts.routing_number', 'like', $search.'%');
                                })
                                ->limit(10)
                                ->get();

        foreach($companies AS $key => $value):
            $companies[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return CompanyPendingResource::collection($companies);
    }

    public function headquarters()
    {
        $companies = Company::orderBy('updated_at', 'DESC')
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->paginate(10);

        foreach($companies AS $key => $value):
            $companies[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return CompanyPendingResource::collection($companies);
    }

    public function headquarters_search($search)
    {
        $companies = Company::select('companies.*')
                                ->orderBy('companies.updated_at', 'DESC')
                                ->groupBy('companies.uuid')
                                ->join('addresses', 'addresses.entity_uuid', '=', 'companies.uuid')
                                ->join('emails', 'emails.entity_uuid', '=', 'companies.uuid')
                                ->join('bank_accounts', 'bank_accounts.entity_uuid', '=', 'companies.uuid')
                                ->where('companies.status', '!=', Config::get('common.status.deleted'))
                                ->where(function ($q) use($search) {
                                    $q->where('companies.legal_name', 'like', '%'.$search.'%')
                                        ->orWhere('companies.ein', 'like', $search.'%')
                                        ->orWhere('companies.business_number', 'like', $search.'%')
                                        ->orWhere('companies.voip_login', 'like', $search.'%')
                                        ->orWhere('companies.business_mobile_number', 'like', $search.'%')
                                        ->orWhere('companies.business_mobile_number_login', 'like', $search.'%')
                                        ->orWhere('companies.website', 'like', $search.'%')
                                        ->orWhere('companies.db_report_number', 'like', $search.'%')
                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.city, ' ', addresses.state) like '%".$search."%'")
                                        ->orWhere('emails.email', 'like', $search.'%')
                                        ->orWhere('emails.phone', 'like', $search.'%')
                                        ->orWhere('bank_accounts.name', 'like', $search.'%')
                                        ->orWhere('bank_accounts.website', 'like', $search.'%')
                                        ->orWhere('bank_accounts.username', 'like', $search.'%')
                                        ->orWhere('bank_accounts.account_number', 'like', $search.'%')
                                        ->orWhere('bank_accounts.routing_number', 'like', $search.'%');
                                })
                                ->limit(10)
                                ->get();

        foreach($companies AS $key => $value):
            $companies[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return CompanyPendingResource::collection($companies);
    }

    public function one(Company $company)
    {
        $company = new CompanyResource($company);
        return $company;
    }

    public function delete(Company $company)
    {
        $company->update(['status' => Config::get('common.status.deleted')]);
        $this->addressService->delete_by_entity($company->uuid);
        $this->emailService->delete_by_entity($company->uuid);
        $this->bankAccountService->delete_by_entity($company->uuid);
    }

    public function search($value)
    {
        $companies = Company::orderBy('created_at', 'DESC')
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->where('legal_name', 'like', '%'.$value.'%')
                                ->paginate(20);
        return CompanyResource::collection($companies);
    }

    public function check($entity)
    {
        $check = [];

        if (isset($entity['legal_name'])){
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
        }

        // Director
        if (isset($entity['director_uuid'])){
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
        }

        // EIN
        if (isset($entity['ein'])){
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
        }

        // Business number
        if (isset($entity['business_number'])){
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
        }

        // Voip Login
        if (isset($entity['voip_login'])){
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
        }

        // Business mobile number type
        if (isset($entity['business_mobile_number'])){
            $check['tmp'] = Company::select('business_mobile_number')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('business_mobile_number', $entity['business_mobile_number'])->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check[$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        // Business mobile number login
        if (isset($entity['business_mobile_number_login'])){
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
        }

        // Website
        if (isset($entity['website'])){
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
        }

        // Db report number
        if (isset($entity['db_report_number'])){
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
        }

        return $check;
    }

    public function check_ignore($entity, $ignore_uuid)
    {
        $check = [];

        if (isset($entity['legal_name'])){
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
        }

        // Director
        if (isset($entity['director_uuid'])){
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
        }

        // EIN
        if (isset($entity['ein'])){
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
        }

        // Business number
        if (isset($entity['business_number'])){
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
        }

        // Voip Login
        if (isset($entity['voip_login'])){
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
        }

        // Business mobile number type
        if (isset($entity['business_mobile_number'])){
            $check['tmp'] = Company::select('business_mobile_number')
                                    ->where('uuid', '!=', $ignore_uuid) 
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('business_mobile_number', $entity['business_mobile_number'])->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check[$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        // Business mobile number login
        if (isset($entity['business_mobile_number_login'])){
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
        }

        // Website
        if (isset($entity['website'])){
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
        }

        // Db report number
        if (isset($entity['db_report_number'])){
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
        }

        return $check;
    }

    public function create($entity)
    {
        $company = Company::create($entity);

        // company name
        $company_fn = $company['legal_name'];
        
        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_add'),
            'status' => Config::get('common.status.actived')
        ]);

        return $company;
    }

    public function update(Company $company, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $company->update($entity);

        // company name
        $company_fn = $company['legal_name'];

        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.updated')),
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

        // company name
        $company_fn = $company['legal_name'];

        // logs
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.pending')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $company_fn . "*", Config::get('common.activity.company.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return $company;
    }

    public function pending_update($uuid, $entity)
    {
        $entity['updated_at'] = Carbon::now();
        $company = Company::where('uuid', $uuid)
                            ->first();

        $entity['status'] = Config::get('common.status.pending');
        $company->update($entity);

        // company name
        $company_fn = $company['legal_name'];

        // logs
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.pending_update')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $company_fn . "*", Config::get('common.activity.company.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return $company;
    }

    public function accept(Company $company, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $company->update($entity);

        // company name
        $company_fn = $company['legal_name'];

        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.accept')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.company_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $company['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $company_fn . "*", Config::get('common.activity.company.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid']. ')'
        ]);

        return $company;
    }

    public function reject($uuid, $user_uuid)
    {
        $company = Company::where('uuid', $uuid)->first();
        $company->update(['status' => Config::get('common.status.rejected')]);

        // company name
        $company_fn = $company['legal_name'];

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.reject')),
            'changes' => '',
            'action_code' => Config::get('common.activity.codes.company_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $company['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $company_fn . "*", Config::get('common.activity.company.reject')) . "\n" .
                        '[link to change](' .env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid']. ')'
        ]);
    }

}