<?php

namespace App\Services\VirtualOffice;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Account\ActivityResource;
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
use Illuminate\Support\Facades\DB;

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

    public function summary($user_uuid = '')
    {
        $entity = [
            'active' => VirtualOffice::where(function ($q){
                                    $q->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->where('vo_active', 'YES');
                                })
                                ->when(($user_uuid!=''), function ($q) use ($user_uuid){
                                    $q->where('user_uuid', $user_uuid);
                                })
                                ->count(),
            'none_active' => VirtualOffice::where(function ($q){
                                    $q->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->where('vo_active', 'NO');
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
        $virtualOffices = VirtualOffice::orderBy('updated_at')
                                        ->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->paginate(20);

        foreach($virtualOffices AS $key => $value):
            $virtualOffices[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return VirtualOfficePendingResource::collection($virtualOffices);
    }

    public function for_pending($user_uuid, $filter, $filter_summary, $filter_by_user)
    {
        $virtualOffices = VirtualOffice::from('virtual_offices as vo1')
                                        ->select('vo1.*')
                                        ->orderBy('vo1.updated_at', 'DESC')
                                        ->groupBy('vo1.uuid')
                                        ->where('vo1.status', '!=', Config::get('common.status.deleted'))
                                        // filter by user activity
                                        ->when(($filter_by_user!=''), function ($gq) use ($filter_by_user){ // filter by user
                                            $gq->leftJoin('activities as a1', function($join) {
                                                $join->on('a1.entity_uuid', '=' , 'vo1.uuid')
                                                        ->where('a1.created_at', '=', function($q){
                                                            $q->from('activities as a2')
                                                                ->select(DB::raw('max(`a2`.`created_at`)'))
                                                                ->where('a2.entity_uuid', '=', DB::raw('`vo1`.`uuid`'));
                                                        });
                                            })
                                            ->where('a1.user_uuid', $filter_by_user);
                                        })
                                        // general filter
                                        ->when(($filter!=''), function ($gq) use ($filter) { // general filter
                                            return $gq->when(($filter=='0'), function ($q) { // normal view
                                                    return $q->where('vo1.status', '!=', Config::get('common.status.deleted'));
                                                })
                                                ->when($filter=='1', function ($q) { // unapproved
                                                    return $q->where('vo1.status', Config::get('common.status.pending'));
                                                })
                                                ->when($filter=='2', function ($q) { // approved
                                                    return $q->where('vo1.status', Config::get('common.status.actived'));
                                                })
                                                ->when($filter=='3', function ($q) { // rejected
                                                    return $q->where('vo1.status', Config::get('common.status.rejected'));
                                                });
                                        })
                                        // summary filter
                                        ->when(($filter_summary!=''), function($gq) use ($filter_summary){ // summary filter
                                            return $gq->when((
                                                            $filter_summary!='11' &&
                                                            $filter_summary!='12'), function ($q){ // not director filter
                                                                return $q->where('vo1.status', 100); // never true
                                                            })
                                                        ->when(($filter_summary=='11'), function ($q){ // active vo
                                                            return $q->where('vo1.vo_active', 'YES');
                                                        }) 
                                                        ->when(($filter_summary=='12'), function ($q){ // none active vo
                                                            return $q->where('vo1.vo_active', 'NO');
                                                        });
                                        })
                                        // user filter
                                        ->when(($user_uuid!=''), function ($q) use($user_uuid){ // by user
                                            return $q->where('vo1.user_uuid', $user_uuid);
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

    public function update(VirtualOffice $virtualOffice, $entity)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['approved'] = Config::get('common.status.actived');
        $virtualOffice->update($entity);

        // get name
        $name = $this->get_name($entity);

        Activity::create([
            'user_uuid' => $virtualOffice['user_uuid'],
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
        $activity = Activity::create([
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

        // telegram
        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $name . "*", Config::get('common.activity.virtual_office.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // push pending
        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        $this->notificationService->push_to_headquarters('pending', ['data' => new VirtualOfficePendingResource($virtualOffice), 'msg' => '', 'link' => '']);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function pending_update(VirtualOffice $virtualOffice, $entity)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['status'] = Config::get('common.status.pending');
        $virtualOffice->update($entity);

        // get name
        $name = $this->get_name($entity);

        // logs
        $activity = Activity::create([
            'user_uuid' => $virtualOffice['user_uuid'],
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

        // telegram
        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $name . "*", Config::get('common.activity.virtual_office.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // push pending
        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        $this->notificationService->push_to_headquarters('pending', ['data' => new VirtualOfficePendingResource($virtualOffice), 'msg' => '', 'link' => '']);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function accept(VirtualOffice $virtualOffice, $entity)
    {
        $notifyUser = $virtualOffice->user_uuid;

        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $virtualOffice->update($entity);

        // get name
        $name = $this->get_name($entity);

        // log
        $activity = Activity::create([
            'user_uuid' => $virtualOffice['user_uuid'],
            'entity_uuid' => $virtualOffice['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.virtual_office.accept')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.virtual_office_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $notifyUser)->first();

        // telegram
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $name . "*", Config::get('common.activity.virtual_office.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/virtual-offices/'.$virtualOffice['uuid']. ')'
        ]);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push('activity', $user, ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // push pending
        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        $this->notificationService->push('pending', $user, ['data' => new VirtualOfficePendingResource($virtualOffice), 'msg' => '', 'link' => '']);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function reject($uuid, $user_uuid)
    {
        $virtualOffice = VirtualOffice::where('uuid', $uuid)->first();

        $entity['status'] = Config::get('common.status.rejected');
        $virtualOffice->update($entity);

        // get name
        $name = $this->get_name($virtualOffice);

        // log
        $activity = Activity::create([
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

        // telegram
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $name . "*", Config::get('common.activity.virtual_office.reject')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/vitual-offices/'.$virtualOffice['uuid']. ')'
        ]);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push('activity', $user, ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // push pending
        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        $this->notificationService->push('pending', $user, ['data' => new VirtualOfficePendingResource($virtualOffice), 'msg' => '', 'link' => '']);

        $virtualOffice['last_activity'] = $this->activityService->by_entity_last($virtualOffice['uuid']);
        return new VirtualOfficePendingResource($virtualOffice);
    }

    public function search($user_uuid, $search)
    {
        $virtualOffices = VirtualOffice::select('virtual_offices.*')
                                ->orderBy('virtual_offices.updated_at', 'DESC')
                                ->groupBy('virtual_offices.uuid')
                                ->leftJoin('addresses', 'addresses.entity_uuid', '=', 'virtual_offices.uuid')
                                ->leftJoin('directors', 'directors.uuid', '=', 'virtual_offices.vo_signer_uuid')
                                ->leftJoin('companies', 'companies.uuid', '=', 'virtual_offices.vo_signer_company_uuid')
                                ->where('virtual_offices.status', '!=', Config::get('common.status.deleted'))
                                ->where(function ($q) use($search) {
                                    $q
                                        // director names
                                        ->whereRaw("concat(directors.first_name, ' ', directors.last_name) like '%".$search."%'")
                                        ->orWhereRaw("concat(directors.first_name, ' ', directors.middle_name, ' ', directors.last_name) like '%".$search."%'")
                                        ->orWhereRaw("concat(directors.last_name, ' ', directors.middle_name, ' ', directors.first_name) like '%".$search."%'")
                                        ->orWhereRaw("concat(directors.last_name, ' ', directors.first_name) like '%".$search."%'")

                                        // company names
                                        ->orWhere('companies.legal_name', 'like', $search.'%')

                                        // basic info
                                        ->orWhere('virtual_offices.vo_website', 'like', $search.'%')
                                        ->orWhere('virtual_offices.vo_contact_person_name', 'like', $search.'%')
                                        ->orWhere('virtual_offices.vo_contact_person_phone_number', 'like', $search.'%')
                                        ->orWhere('virtual_offices.vo_contact_person_email', 'like', $search.'%')
                                        ->orWhere('virtual_offices.online_email', 'like', $search.'%')
                                        ->orWhere('virtual_offices.online_account_username', 'like', $search.'%')
                                        ->orWhere('virtual_offices.card_holder_name', 'like', $search.'%')
                                        ->orWhere('virtual_offices.vo_provider_name', 'like', $search.'%')
                                        ->orWhere('virtual_offices.vo_provider_phone_number', 'like', $search.'%')

                                        // addresses
                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.address_line_2, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal, ' ', addresses.country) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal, ' ', addresses.country) like '%".$search."%'")

                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.address_line_2, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal, ', ', addresses.country) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal, ', ', addresses.country) like '%".$search."%'");
                                })
                                ->when(($user_uuid!=''), function ($q) use ($user_uuid){
                                    return $q->where('virtual_offices.user_uuid', $user_uuid);
                                })
                                ->limit(5)
                                ->get();

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
        if (isset($entity['vo_signer_company_uuid']) && $entity['vo_signer_company_uuid']!=''){
            $company = $this->companyService->by_uuid($entity['vo_signer_company_uuid']);
            if ($company!=null){
                $name = 'for company ' . $company['legal_name'];
            }
        }
        return $name;
    }

}