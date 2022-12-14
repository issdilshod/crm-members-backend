<?php

namespace App\Services\Company;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Account\ActivityResource;
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
use Illuminate\Support\Facades\DB;

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

    public function summary($user_uuid = '')
    {
        $entity = [
            'all' => Company::where('status', '!=', Config::get('common.status.deleted'))
                                ->where('user_uuid', 'like', $user_uuid . '%')
                                ->count(),
            'approved' => Company::where('status', Config::get('common.status.actived'))
                                    ->where('user_uuid', 'like', $user_uuid . '%')
                                    ->count(),
            'pending' => Company::where(function ($q){
                                    $q->where('status', Config::get('common.status.pending'))
                                        ->orWhere('status', Config::get('common.status.rejected'));
                                })
                                ->when(($user_uuid!=''), function ($q) use ($user_uuid){
                                    $q->where('user_uuid', $user_uuid);
                                })
                                ->count(),
            'active' => Company::where(function ($q){
                                    $q->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->where('is_active', 'YES');
                                })
                                ->when(($user_uuid!=''), function ($q) use ($user_uuid){
                                    $q->where('user_uuid', $user_uuid);
                                })
                                ->count(),
            'none_active' => Company::where(function ($q){
                                    $q->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->where('is_active', 'NO');
                                })
                                ->when(($user_uuid!=''), function ($q) use ($user_uuid){
                                    $q->where('user_uuid', $user_uuid);
                                })
                                ->count(),
        ];

        return $entity;
    }

    public function all()
    {
        $companies = Company::orderBy('legal_name', 'ASC')
                                ->orderBy('created_at', 'DESC')
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->where('approved', Config::get('common.status.actived'))
                                ->paginate(20);

        foreach($companies AS $key => $value):
            $companies[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;
        
        return CompanyPendingResource::collection($companies);
    }

    public function for_pending($user_uuid = '', $filter, $summary_filter, $filter_by_user)
    {
        $companies = Company::from('companies as c1')
                            ->select('c1.*')
                            ->orderBy('c1.updated_at', 'DESC')
                            ->groupBy('c1.uuid')
                            ->where('c1.status', '!=', Config::get('common.status.deleted'))
                            // filter by user activity
                            ->when(($filter_by_user!=''), function ($gq) use ($filter_by_user){ // filter by user
                                $gq->leftJoin('activities as a1', function($join) {
                                    $join->on('a1.entity_uuid', '=' , 'c1.uuid')
                                            ->where('a1.created_at', '=', function($q){
                                                $q->from('activities as a2')
                                                    ->select(DB::raw('max(`a2`.`created_at`)'))
                                                    ->where('a2.entity_uuid', '=', DB::raw('`c1`.`uuid`'));
                                            });
                                })
                                ->where('a1.user_uuid', $filter_by_user);
                            })
                            // general filter
                            ->when(($filter!=''), function ($gq) use ($filter) { // general filter
                                return $gq->when(($filter=='0') , function ($q) { // normal view
                                    return $q->where('c1.status', '!=', Config::get('common.status.deleted'));
                                })
                                ->when($filter=='1', function ($q) { // unapproved
                                    return $q->where('c1.status', Config::get('common.status.pending'));
                                })
                                ->when($filter=='2', function ($q) { // approved
                                    return $q->where('c1.status', Config::get('common.status.actived'));
                                })
                                ->when($filter=='3', function ($q) { // rejected
                                    return $q->where('c1.status', Config::get('common.status.rejected'));
                                });
                            })
                            // summary filter
                            ->when(($summary_filter!=''), function ($gq) use ($summary_filter) { // summary filter
                                return $gq->when((
                                                $summary_filter!='6' &&
                                                $summary_filter!='7' && 
                                                $summary_filter!='8' &&
                                                $summary_filter!='9' &&
                                                $summary_filter!='10'), function ($q){ // not company filter
                                                    return $q->where('c1.status', 100); // never true
                                                })
                                            ->when(($summary_filter=='6'), function($q){ /* all companies, no need to query */ })
                                            ->when(($summary_filter=='7'), function($q){ // approved companies
                                                return $q->where('c1.status', Config::get('common.status.actived'));
                                            })
                                            ->when(($summary_filter=='8'), function($q){ // pending companies
                                                return $q->where(function ($qq) {
                                                            $qq->where('c1.status', Config::get('common.status.pending'))
                                                                ->orWhere('c1.status', Config::get('common.status.rejected'));
                                                        });
                                            })
                                            ->when(($summary_filter=='9'), function($q){ // active companies
                                                return $q->where('c1.is_active', 'YES');
                                            })
                                            ->when(($summary_filter=='10'), function($q){ // none active companies
                                                return $q->where('c1.is_active', 'NO');
                                            });
                            })
                            // user filter
                            ->when(($user_uuid!=''), function ($q) use ($user_uuid){
                                return $q->where('c1.user_uuid', $user_uuid);
                            })
                            ->paginate(5);

        foreach($companies AS $key => $value):
            $companies[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return CompanyPendingResource::collection($companies);
    }

    public function for_pending_search($user_uuid = '', $search)
    {
        $companies = Company::select('companies.*')
                                ->orderBy('companies.updated_at', 'DESC')
                                ->groupBy('companies.uuid')
                                ->leftJoin('addresses', 'addresses.entity_uuid', '=', 'companies.uuid')
                                ->leftJoin('emails', 'emails.entity_uuid', '=', 'companies.uuid')
                                ->leftJoin('bank_accounts', 'bank_accounts.entity_uuid', '=', 'companies.uuid')
                                ->leftJoin('states as states1', 'states1.uuid', '=', 'companies.incorporation_state_uuid')
                                ->leftJoin('states as states2', 'states2.uuid', '=', 'companies.doing_business_in_state_uuid')
                                ->leftJoin('hostings', 'hostings.uuid', '=', 'emails.hosting_uuid')
                                ->where('companies.status', '!=', Config::get('common.status.deleted'))
                                ->where(function ($q) use($search) {
                                    $q
                                        // name
                                        ->where('companies.legal_name', 'like', '%'.$search.'%')
                                        
                                        // incoroporation
                                        ->orWhere('companies.incorporation_date', 'like', '%'.$search.'%')
                                        ->orWhere('companies.incorporation_state_name', 'like', '%'.$search.'%')
                                        ->orWhere('companies.doing_business_in_state_name', 'like', '%'.$search.'%')

                                        // basic info
                                        ->orWhere('companies.ein', 'like', '%'.$search.'%')

                                        // phones
                                        ->orWhere('companies.business_number', 'like', '%'.$search.'%')
                                        ->orWhere('companies.business_mobile_number', 'like', '%'.$search.'%')
                                        ->orWhere('companies.business_mobile_login', 'like', '%'.$search.'%')
                                        ->orWhere('companies.voip_login', 'like', '%'.$search.'%')
                                        ->orWhere('companies.website', 'like', '%'.$search.'%')
                                        ->orWhere('companies.db_report_number', 'like', '%'.$search.'%')

                                        // addresses
                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.address_line_2, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal, ' ', addresses.country) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal, ' ', addresses.country) like '%".$search."%'")

                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.address_line_2, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal, ', ', addresses.country) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal, ', ', addresses.country) like '%".$search."%'")

                                        // states
                                        ->orWhere('states1.full_name', '%'.$search.'%')
                                        ->orWhere('states2.full_name', '%'.$search.'%')
                                        ->orWhere('states1.short_name', '%'.$search.'%')
                                        ->orWhere('states2.short_name', '%'.$search.'%')

                                        // emails
                                        ->orWhere('hostings.host', 'like', '%'.$search.'%')
                                        ->orWhere('emails.email', 'like', '%'.$search.'%')
                                        ->orWhere('emails.phone', 'like', '%'.$search.'%')
                                        
                                        // bank account
                                        ->orWhere('bank_accounts.name', 'like', '%'.$search.'%')
                                        ->orWhere('bank_accounts.website', 'like', '%'.$search.'%')
                                        ->orWhere('bank_accounts.username', 'like', '%'.$search.'%')
                                        ->orWhere('bank_accounts.account_number', 'like', '%'.$search.'%')
                                        ->orWhere('bank_accounts.routing_number', 'like', '%'.$search.'%');
                                })
                                ->when(($user_uuid!=''), function ($q) use($user_uuid) {
                                    return $q->where('companies.user_uuid', $user_uuid);
                                })
                                ->limit(5)
                                ->get();

        foreach($companies AS $key => $value):
            $companies[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return CompanyPendingResource::collection($companies);
    }

    public function for_pending_related($directors)
    {
        $idS = [];
        foreach($directors AS $key => $value):
            $idS[] = $value['uuid'];
        endforeach;

        $companies = Company::where('status', '!=', Config::get('common.status.deleted'))->whereIn('director_uuid', $idS)->get();

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

    public function check($entity, $ignore_uuid = '')
    {
        $check = [];

        // director
        if (isset($entity['director_uuid'])){
            $check['tmp'] = Company::select('director_uuid', 'legal_name')
                                    ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                        return $q->where('uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('director_uuid', $entity['director_uuid'])
                                    ->first();
            
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    if ($this->is_idefier($key)){ continue; }
                    $check[$key] = Config::get('common.errors.exsist') . $this->message_where_exists($check['tmp']);
                endforeach;
            }

            unset($check['tmp']);
        }

        // EIN
        if (isset($entity['ein'])){
            $check['tmp'] = Company::select('ein', 'legal_name')
                                    ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                        return $q->where('uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('ein', $entity['ein'])
                                    ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    if ($this->is_idefier($key)){ continue; }
                    $check[$key] = Config::get('common.errors.exsist') . $this->message_where_exists($check['tmp']);
                endforeach;
            }

            unset($check['tmp']);
        }

        // business number
        if (isset($entity['business_number'])){
            $check['tmp'] = Company::select('business_number', 'legal_name')
                                    ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                        return $q->where('uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('business_number', $entity['business_number'])
                                    ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    if ($this->is_idefier($key)){ continue; }
                    $check[$key] = Config::get('common.errors.exsist') . $this->message_where_exists($check['tmp']);
                endforeach;
            }

            unset($check['tmp']);
        }

        // business mobile number
        if (isset($entity['business_mobile_number'])){
            $check['tmp'] = Company::select('business_mobile_number', 'legal_name')
                                    ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                        return $q->where('uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('business_mobile_number', $entity['business_mobile_number'])
                                    ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    if ($this->is_idefier($key)){ continue; }
                    $check[$key] = Config::get('common.errors.exsist') . $this->message_where_exists($check['tmp']);
                endforeach;
            }

            unset($check['tmp']);
        }

        // business mobile login
        if (isset($entity['business_mobile_login'])){
            $check['tmp'] = Company::select('business_mobile_login', 'legal_name')
                                    ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                        return $q->where('uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('business_mobile_login', $entity['business_mobile_login'])
                                    ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    if ($this->is_idefier($key)){ continue; }
                    $check[$key] = Config::get('common.errors.exsist') . $this->message_where_exists($check['tmp']);
                endforeach;
            }

            unset($check['tmp']);
        }

        // website
        if (isset($entity['website'])){
            $check['tmp'] = Company::select('website', 'legal_name')
                                    ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                        return $q->where('uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('website', $entity['website'])
                                    ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['website_c'] AS $key => $value):
                    if ($this->is_idefier($key)){ continue; }
                    $check[$key] = Config::get('common.errors.exsist') . $this->message_where_exists($check['tmp']);
                endforeach;
            }

            unset($check['tmp']);
        }

        // db report number
        if (isset($entity['db_report_number'])){
            $check['tmp'] = Company::select('db_report_number', 'legal_name')
                                    ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                        return $q->where('uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('db_report_number', $entity['db_report_number'])
                                    ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    if ($this->is_idefier($key)){ continue; }
                    $check[$key] = Config::get('common.errors.exsist') . $this->message_where_exists($check['tmp']);
                endforeach;
            }
            unset($check['tmp']);

        }

        return $check;
    }

    public function create($entity)
    {
        $entity['approved'] = Config::get('common.status.actived');
        $company = Company::create($entity);

        // company name
        $company_fn = $company['legal_name'];
        
        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.add')),
            'changes' => json_encode(new CompanyResource($company)),
            'action_code' => Config::get('common.activity.codes.company_add'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        $company['last_activity'] = $this->activityService->by_entity_last($company->uuid);
        return new CompanyPendingResource($company);
    }

    public function update(Company $company, $entity)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['approved'] = Config::get('common.status.actived');
        $company->update($entity);

        // company name
        $company_fn = $company['legal_name'];

        $activity = Activity::create([
            'user_uuid' => $company['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.updated')),
            'changes' => json_encode(new CompanyResource($company)),
            'action_code' => Config::get('common.activity.codes.company_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        $company['last_activity'] = $this->activityService->by_entity_last($company->uuid);
        return new CompanyPendingResource($company);
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $company = Company::create($entity);

        // company name
        $company_fn = $company['legal_name'];

        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.pending')),
            'changes' => json_encode(new CompanyResource($company)),
            'action_code' => Config::get('common.activity.codes.company_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $company_fn . "*", Config::get('common.activity.company.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // push pending
        $company['last_activity'] = $this->activityService->by_entity_last($company['uuid']);
        $this->notificationService->push_to_headquarters('pending', ['data' => new CompanyPendingResource($company), 'msg' => '', 'link' => '']);

        $company['last_activity'] = $this->activityService->by_entity_last($company->uuid);
        return new CompanyPendingResource($company);
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

        $activity = Activity::create([
            'user_uuid' => $company['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.pending_update')),
            'changes' => json_encode(new CompanyResource($company)),
            'action_code' => Config::get('common.activity.codes.company_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // notification
        $user = User::where('uuid', $company['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $company_fn . "*", Config::get('common.activity.company.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // push pending
        $company['last_activity'] = $this->activityService->by_entity_last($company['uuid']);
        $this->notificationService->push_to_headquarters('pending', ['data' => new CompanyPendingResource($company), 'msg' => '', 'link' => '']);

        $company['last_activity'] = $this->activityService->by_entity_last($company->uuid);
        return new CompanyPendingResource($company);
    }

    public function accept(Company $company, $entity, $override = false)
    {
        $notifUser = $company->user_uuid;

        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $company->update($entity);

        // company name
        $company_fn = $company['legal_name'];

        $activity = Activity::create([
            'user_uuid' => $company['user_uuid'],
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, ($override?Config::get('common.activity.company.override'):Config::get('common.activity.company.accept'))),
            'changes' => json_encode(new CompanyResource($company)),
            'action_code' => Config::get('common.activity.codes.company_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $notifUser)->first();

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push('activity', $user, ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // telegram
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $company_fn . "*", Config::get('common.activity.company.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid']. ')'
        ]);

        // push pending
        $company['last_activity'] = $this->activityService->by_entity_last($company['uuid']);
        $this->notificationService->push('pending', $user, ['data' => new CompanyPendingResource($company), 'msg' => '', 'link' => '']);

        $company['last_activity'] = $this->activityService->by_entity_last($company->uuid);
        return new CompanyPendingResource($company);
    }

    public function reject($uuid, $user_uuid)
    {
        $company = Company::where('uuid', $uuid)->first();
        $company->update(['status' => Config::get('common.status.rejected')]);

        // company name
        $company_fn = $company['legal_name'];

        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $company['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $company_fn, Config::get('common.activity.company.reject')),
            'changes' => json_encode(new CompanyResource($company)),
            'action_code' => Config::get('common.activity.codes.company_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $company['user_uuid'])->first();

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push('activity', $user, ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // telegram
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $company_fn . "*", Config::get('common.activity.company.reject')) . "\n" .
                        '[link to change](' .env('APP_FRONTEND_ENDPOINT').'/companies/'.$company['uuid']. ')'
        ]);

        // push pending
        $company['last_activity'] = $this->activityService->by_entity_last($company['uuid']);
        $this->notificationService->push('pending', $user, ['data' => new CompanyPendingResource($company), 'msg' => '', 'link' => '']);

        return new CompanyPendingResource($company);
    }

    public function by_director($director_uuid)
    {
        $company = Company::where('director_uuid', $director_uuid)
                            ->where('status', '!=', Config::get('common.status.deleted'))
                            ->first(['uuid', 'legal_name']);
        return $company;
    }

    public function company_list($value = '')
    {
        $companies = Company::orderBy('legal_name', 'ASC')
                            ->where('status', '!=', Config::get('common.status.deleted'))
                            ->where('approved', Config::get('common.status.actived'))
                            ->where('legal_name', 'like', '%'. $value .'%')
                            ->limit(20)
                            ->get(['uuid', 'legal_name']);
        return $companies;
    }

    public function by_uuid($uuid)
    {
        $company = Company::where('uuid', $uuid)
                            ->where('status', '!=', Config::get('common.status.deleted'))
                            ->first();

        return $company;
    }

    private function is_idefier($check)
    {
        $idenfier = ['legal_name'];
        $is_idefier = false;
        foreach ($idenfier AS $key => $value):
            if ($value==$check){
                $is_idefier = true;
            }
        endforeach;
        return $is_idefier;
    }

    private function message_where_exists($entity)
    {
        return ' on company card ' . strtoupper($entity['legal_name']);
    }

}