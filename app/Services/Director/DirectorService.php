<?php

namespace App\Services\Director;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Director\DirectorPendingResource;
use App\Http\Resources\Director\DirectorResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Company\Company;
use App\Models\Director\Director;
use App\Services\Account\ActivityService;
use App\Services\Helper\AddressService;
use App\Services\Helper\EmailService;
use App\Services\Helper\NotificationService;
use Carbon\Carbon;
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

    public function summary($uuid = '')
    {
        $entity = [
            'all' => Director::where('status', '!=', Config::get('common.status.deleted'))
                                ->where('user_uuid', 'like', $uuid . '%')
                                ->count(),
            'active' => Director::where('status', Config::get('common.status.actived'))
                                    ->where('user_uuid', 'like', $uuid . '%')
                                    ->count(),
            'pending' => Director::where('status', Config::get('common.status.pending'))
                                    ->where('user_uuid', 'like', $uuid . '%')
                                    ->count()
        ];

        return $entity;
    }

    public function all()
    {
        $directors = Director::orderBy('first_name', 'ASC')
                                ->orderBy('middle_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->orderBy('updated_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(20);
        return DirectorResource::collection($directors);
    }

    public function by_user($user_uuid, $filter)
    {
        $directors = Director::orderBy('updated_at', 'DESC')
                                ->when(($filter!='' || $filter=='0') , function ($q) { // normal view
                                    return $q->where('status', '!=', Config::get('common.status.deleted'));
                                })
                                ->when($filter=='1', function ($q) { // unapproved
                                    return $q->where('status', Config::get('common.status.pending'));
                                })
                                ->when($filter=='2', function ($q) { // approved
                                    return $q->where('status', Config::get('common.status.actived'));
                                })
                                ->when($filter=='3', function ($q) { // rejected
                                    return $q->where('status', Config::get('common.status.rejected'));
                                })
                                ->where('user_uuid', $user_uuid)
                                ->paginate(10);

        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return DirectorPendingResource::collection($directors);
    }

    public function by_user_search($user_uuid, $search)
    {
        $directors = Director::select('directors.*')
                                ->orderBy('directors.updated_at', 'DESC')
                                ->groupBy('directors.uuid')
                                ->join('addresses', 'addresses.entity_uuid', '=', 'directors.uuid')
                                ->join('emails', 'emails.entity_uuid', '=', 'directors.uuid')
                                ->where('directors.status', '!=', Config::get('common.status.deleted'))
                                ->where('directors.user_uuid', $user_uuid)
                                ->where(function ($q) use($search) {
                                    $q->whereRaw("concat(directors.first_name, ' ', directors.last_name) like '%".$search."%'")
                                        ->whereRaw("concat(directors.first_name, ' ', directors.middle_name, ' ', directors.last_name) like '%".$search."%'")
                                        ->whereRaw("concat(directors.last_name, ' ', directors.first_name) like '%".$search."%'")

                                        ->orWhere('directors.date_of_birth', 'like', $search.'%')
                                        ->orWhere('directors.ssn_cpn', 'like', $search.'%')
                                        ->orWhere('directors.company_association', 'like', $search.'%')
                                        ->orWhere('directors.phone_number', 'like', $search.'%')

                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.address_line_2, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.address_line_2, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal) like '%".$search."%'")

                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal) like '%".$search."%'")

                                        ->orWhere('emails.email', 'like', $search.'%')
                                        ->orWhere('emails.phone', 'like', $search.'%');
                                })
                                ->limit(10)
                                ->get();

        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return DirectorPendingResource::collection($directors);
    }

    public function headquarters($filter)
    {
        $directors = Director::orderBy('updated_at', 'DESC')
                                ->when(($filter!='' || $filter=='0') , function ($q) { // normal view
                                    return $q->where('status', '!=', Config::get('common.status.deleted'));
                                })
                                ->when($filter=='1', function ($q) { // unapproved
                                    return $q->where('status', Config::get('common.status.pending'));
                                })
                                ->when($filter=='2', function ($q) { // approved
                                    return $q->where('status', Config::get('common.status.actived'));
                                })
                                ->when($filter=='3', function ($q) { // rejected
                                    return $q->where('status', Config::get('common.status.rejected'));
                                })
                                ->paginate(10);       

        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return DirectorPendingResource::collection($directors);
    }

    public function headquarters_search($search)
    {
        $directors = Director::select('directors.*')
                                ->orderBy('directors.updated_at', 'DESC')
                                ->groupBy('directors.uuid')
                                ->join('addresses', 'addresses.entity_uuid', '=', 'directors.uuid')
                                ->join('emails', 'emails.entity_uuid', '=', 'directors.uuid')
                                ->where('directors.status', '!=', Config::get('common.status.deleted'))
                                ->where(function ($q) use($search) {
                                    $q->whereRaw("concat(directors.first_name, ' ', directors.last_name) like '%".$search."%'")
                                        ->whereRaw("concat(directors.first_name, ' ', directors.middle_name, ' ', directors.last_name) like '%".$search."%'")
                                        ->whereRaw("concat(directors.last_name, ' ', directors.first_name) like '%".$search."%'")

                                        ->orWhere('directors.date_of_birth', 'like', $search.'%')
                                        ->orWhere('directors.ssn_cpn', 'like', $search.'%')
                                        ->orWhere('directors.company_association', 'like', $search.'%')
                                        ->orWhere('directors.phone_number', 'like', $search.'%')

                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.address_line_2, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.address_line_2, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal) like '%".$search."%'")

                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal) like '%".$search."%'")

                                        ->orWhere('emails.email', 'like', $search.'%')
                                        ->orWhere('emails.phone', 'like', $search.'%');
                                })
                                ->limit(10)
                                ->get();

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
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->whereRaw("concat(first_name, ' ', last_name) like '%".$value."%'")
                                ->paginate(20);
        return DirectorResource::collection($directors);
    }

    private function is_idefier($check)
    {
        $idenfier = ['first_name', 'middle_name', 'last_name'];
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
        return ' On director card *' . $entity['first_name'] . ' ' . $entity['middle_name'] . ' ' . $entity['last_name'] . '*';
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
        if (isset($entity['ssn_cpn'])){
            $check['tmp'] = Director::select('ssn_cpn', 'first_name', 'middle_name', 'last_name')
                                        ->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->where('ssn_cpn', $entity['ssn_cpn'])
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

        // Company 
        /*$check['tmp'] = Director::select('company_association')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('company_association', $entity['company_association'])
                                    ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);*/

        // Phone
        if (isset($entity['phone_number'])){
            $check['tmp'] = Director::select('phone_number', 'first_name', 'middle_name', 'last_name')
                                        ->where('status', '!=', Config::get('common.status.deleted'))
                                        ->where('approved', Config::get('common.status.actived'))
                                        ->where('phone_number', $entity['phone_number'])
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
        if (isset($entity['ssn_cpn'])){
            $check['tmp'] = Director::select('ssn_cpn', 'first_name', 'middle_name', 'last_name')
                                    ->where('uuid', '!=', $ignore_uuid)
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('ssn_cpn', $entity['ssn_cpn'])
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

        // Company 
        /*$check['tmp'] = Director::select('company_association')
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
        unset($check['tmp']);*/

        // Phone
        if (isset($entity['phone_number'])){
            $check['tmp'] = Director::select('phone_number', 'first_name', 'middle_name', 'last_name')
                                    ->where('uuid', '!=', $ignore_uuid)
                                    ->where('status', '!=', Config::get('common.status.deleted'))
                                    ->where('approved', Config::get('common.status.actived'))
                                    ->where('phone_number', $entity['phone_number'])
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
        $director = Director::create($entity);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];

        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_add'),
            'status' => Config::get('common.status.actived')
        ]);

        return $director;
    }

    public function update(Director $director, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['approved'] = Config::get('common.status.actived');
        $director->update($entity);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];

        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.update')),
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

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];

        // logs
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.pending')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $director_fn . "*", Config::get('common.activity.director.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return $director;
    }

    public function pending_update($uuid, $entity, $user_uuid)
    {
        $director = Director::where('uuid', $uuid)
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->first();

        $entity['status'] = Config::get('common.status.pending');
        $entity['updated_at'] = Carbon::now();
        $director->update($entity);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.pending_update')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $user_uuid)->first();

        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $director_fn . "*", Config::get('common.activity.director.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        return $director;
    }

    public function accept(Director $director, $entity, $user_uuid, $override = false)
    {
        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $director->update($entity);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];
        
        // log
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, ($override?Config::get('common.activity.director.override'):Config::get('common.activity.director.accept'))),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $director['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $director_fn . "*", Config::get('common.activity.director.accept')) . "\n" .
            '[link to view]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')'
        ]);

        return $director;
    }

    public function reject($uuid, $user_uuid)
    {
        $director = Director::where('uuid', $uuid)->first();
        $director->update(['status' => Config::get('common.status.rejected')]);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];

        // logs
        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.reject')),
            'changes' => '',
            'action_code' => Config::get('common.activity.codes.director_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $director['user_uuid'])->first();

        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $director_fn . "*", Config::get('common.activity.director.reject')) . "\n" .
                        '[link to change]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')'
        ]);

    }

    public function director_list($value = '')
    {
        $directors = Director::orderBy('first_name', 'ASC')
                                ->orderBy('middle_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->orderBy('updated_at', 'DESC')
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->where('approved', Config::get('common.status.actived'))
                                ->whereRaw("concat(first_name, ' ', last_name) like '%".$value."%'")
                                ->limit(20)
                                ->get(['uuid', 'first_name', 'middle_name', 'last_name']);
        return $directors;
    }

}