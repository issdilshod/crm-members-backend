<?php

namespace App\Services\Director;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Account\ActivityResource;
use App\Http\Resources\Director\DirectorListResource;
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
use Illuminate\Support\Facades\DB;

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
            'pending' => Director::where(function ($q){
                                        $q->where('status', Config::get('common.status.pending'))
                                            ->orWhere('status', Config::get('common.status.rejected'));
                                    })
                                    ->when(($uuid!=''), function ($q) use ($uuid){
                                        $q->where('user_uuid', $uuid);
                                    })
                                    ->count(),
            'avialable' => count(Director::from('directors as d1')->select('d1.*')
                                        ->where('d1.status', '!=', Config::get('common.status.deleted'))
                                        ->where('d1.user_uuid', 'like', $uuid . '%')
                                        ->leftJoin('companies as c1', 'c1.director_uuid', '=', 'd1.uuid')
                                        ->whereNull('c1.director_uuid')
                                        ->groupBy('d1.uuid')
                                        ->get()),
            'has_company' => count(Director::where('directors.status', '!=', Config::get('common.status.deleted'))
                                    ->where('directors.user_uuid', 'like', $uuid . '%')
                                    ->join('companies', 'companies.director_uuid', '=', 'directors.uuid')
                                    ->groupBy('directors.uuid')
                                    ->get()),
            'with_id' => count(Director::where('directors.status', '!=', Config::get('common.status.deleted'))
                                    ->join('files', 'files.entity_uuid', '=', 'directors.uuid')
                                    ->where(function ($q){
                                        $q->where('files.file_parent', 'dl_upload__back') // dl back
                                            ->orWhere('files.file_parent', 'dl_upload__front'); // dl front
                                    })
                                    ->groupBy('directors.uuid')
                                    ->where('directors.user_uuid', 'like', $uuid . '%')
                                    ->get()),
            'without_id' => count(DB::select(DB::raw('SELECT * FROM directors d1 LEFT JOIN files f1 ON f1.entity_uuid=d1.uuid WHERE d1.status!=0 AND NOT EXISTS (SELECT * FROM files f2 WHERE f2.entity_uuid=d1.uuid AND (f2.file_parent="dl_upload__back" OR f2.file_parent="dl_upload__front")) GROUP BY d1.uuid'))), // TODO: Change to eloquent
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
    
        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;
        
        return DirectorPendingResource::collection($directors);
    }

    public function for_pending($user_uuid, $filter, $filter_summary, $filter_by_user)
    {
        $directors = Director::from('directors as d1')
                            ->select('d1.*')
                            ->orderBy('d1.updated_at', 'DESC')
                            ->groupBy('d1.uuid')
                            ->where('d1.status', '!=', Config::get('common.status.deleted'))
                            ->where('d1.approved', Config::get('common.status.actived'))
                            // filter by user activity
                            ->when(($filter_by_user!=''), function ($gq) use ($filter_by_user){ // filter by user
                                $gq->leftJoin('activities as a1', function($join) {
                                    $join->on('a1.entity_uuid', '=' , 'd1.uuid')
                                            ->where('a1.created_at', '=', function($q){
                                                $q->from('activities as a2')
                                                    ->select(DB::raw('max(`a2`.`created_at`)'))
                                                    ->where('a2.entity_uuid', '=', DB::raw('`d1`.`uuid`'));
                                            });
                                })
                                ->where('a1.user_uuid', $filter_by_user);
                            })
                            // general filter
                            ->when(($filter!=''), function ($gq) use ($filter) { // general filter
                                return $gq->when(($filter=='0'), function ($q) { // normal view
                                        return $q->where('d1.status', '!=', Config::get('common.status.deleted'));
                                    })
                                    ->when($filter=='1', function ($q) { // unapproved
                                        return $q->where('d1.status', Config::get('common.status.pending'));
                                    })
                                    ->when($filter=='2', function ($q) { // approved
                                        return $q->where('d1.status', Config::get('common.status.actived'));
                                    })
                                    ->when($filter=='3', function ($q) { // rejected
                                        return $q->where('d1.status', Config::get('common.status.rejected'));
                                    });
                            })
                            // summary filter
                            ->when(($filter_summary!=''), function ($gq) use ($filter_summary){ // summary filter
                                return $gq->when((
                                                $filter_summary!='0' &&
                                                $filter_summary!='1' && 
                                                $filter_summary!='2' &&
                                                $filter_summary!='3' &&
                                                $filter_summary!='4' &&
                                                $filter_summary!='5'), function ($q){ // not director filter
                                                    return $q->where('d1.status', 100); // never true
                                                })
                                            ->when(($filter_summary=='0'), function ($q){ // all directors
                                                return $q->where('d1.status', '!=', Config::get('common.status.deleted'));
                                            }) 
                                            ->when(($filter_summary=='1'), function ($q){ // approved directors
                                                return $q->where('d1.status', Config::get('common.status.actived'));
                                            }) 
                                            ->when(($filter_summary=='2'), function ($q){ // pending directors
                                                return $q->where(function ($qq) {
                                                            $qq->where('d1.status', Config::get('common.status.pending'))
                                                                ->orWhere('d1.status', Config::get('common.status.rejected'));
                                                        });
                                            }) 
                                            ->when(($filter_summary=='3'), function ($q){ // available directors
                                                return $q->leftJoin('companies as c1', 'c1.director_uuid', '=', 'd1.uuid')
                                                        ->where('d1.status', '!=', Config::get('common.status.deleted'))
                                                        ->whereNull('c1.director_uuid')
                                                        ->groupBy('d1.uuid');
                                            })   
                                            ->when(($filter_summary=='4'), function ($q){ // directors with ID
                                                return $q->where('d1.status', '!=', Config::get('common.status.deleted'))
                                                            ->join('files', 'files.entity_uuid', '=', 'd1.uuid')
                                                            ->where(function ($qq){
                                                                $qq->where('files.file_parent', 'dl_upload__back') // dl back
                                                                    ->orWhere('files.file_parent', 'dl_upload__front'); // dl front
                                                            })
                                                            ->groupBy('d1.uuid');
                                            }) 
                                            ->when(($filter_summary=='5'), function ($q){ // directors without ID
                                                return $q->leftJoin('files as f1', 'f1.entity_uuid', '=', 'd1.uuid')
                                                            ->where('d1.status', '!=', Config::get('common.status.deleted'))
                                                            ->whereNotExists(function($qq){
                                                                $qq->from('files as f2')
                                                                    ->select('f2.*')
                                                                    ->whereColumn('f2.entity_uuid', 'd1.uuid')
                                                                    ->where(function ($qqq){
                                                                        $qqq->where('f2.file_parent', 'dl_upload__back')
                                                                            ->orWhere('f2.file_parent', 'dl_upload__front');
                                                                    });
                                                            })
                                                            ->groupBy('d1.uuid');
                                            });
                                            
                            })
                            // filter by user
                            ->when(($user_uuid!=''), function ($q) use($user_uuid){
                                return $q->where('d1.user_uuid', $user_uuid);
                            })
                            ->paginate(5);

        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return DirectorPendingResource::collection($directors);
    }

    public function for_pending_search($user_uuid = '', $search)
    {
        $directors = Director::select('directors.*')
                                ->orderBy('directors.updated_at', 'DESC')
                                ->groupBy('directors.uuid')
                                ->leftJoin('addresses', 'addresses.entity_uuid', '=', 'directors.uuid')
                                ->leftJoin('emails', 'emails.entity_uuid', '=', 'directors.uuid')
                                ->where('directors.status', '!=', Config::get('common.status.deleted'))
                                ->where(function ($q) use($search) {
                                    $q
                                        // names
                                        ->whereRaw("concat(directors.first_name, ' ', directors.last_name) like '%".$search."%'")
                                        ->orWhereRaw("concat(directors.first_name, ' ', directors.middle_name, ' ', directors.last_name) like '%".$search."%'")
                                        ->orWhereRaw("concat(directors.last_name, ' ', directors.middle_name, ' ', directors.first_name) like '%".$search."%'")
                                        ->orWhereRaw("concat(directors.last_name, ' ', directors.first_name) like '%".$search."%'")

                                        // basic info
                                        ->orWhere('directors.date_of_birth', 'like', $search.'%')
                                        ->orWhere('directors.ssn_cpn', 'like', $search.'%')
                                        ->orWhere('directors.phone_number', 'like', $search.'%')

                                        // addresses
                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.address_line_2, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal, ' ', addresses.country) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ' ', addresses.city, ' ', addresses.state, ' ', addresses.postal, ' ', addresses.country) like '%".$search."%'")

                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.address_line_2, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal, ', ', addresses.country) like '%".$search."%'")
                                        ->orWhereRaw("concat(addresses.street_address, ', ', addresses.city, ', ', addresses.state, ', ', addresses.postal, ', ', addresses.country) like '%".$search."%'")

                                        // emails
                                        ->orWhere('emails.email', 'like', $search.'%')
                                        ->orWhere('emails.phone', 'like', $search.'%');
                                })
                                ->when(($user_uuid!=''), function ($q) use ($user_uuid){
                                    return $q->where('directors.user_uuid', $user_uuid);
                                })
                                ->limit(5)
                                ->get();

        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return DirectorPendingResource::collection($directors);
    }

    public function for_pending_related($companies)
    {
        $idS = [];
        foreach($companies AS $key => $value):
            $idS[] = $value['director_uuid'];
        endforeach;

        $directors = Director::where('status', '!=', Config::get('common.status.deleted'))->whereIn('uuid', $idS)->get();

        foreach($directors AS $key => $value):
            $directors[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;
        
        return DirectorPendingResource::collection($directors);
    }

    public function for_pending_duplicate($user_uuid = '')
    {
        $directors = Director::from('directors as d1')
                        ->select('d1.*')

                        ->join('directors as d2', 'd2.uuid', '!=', 'd1.uuid')

                        ->where(function ($q){
                            $q->whereColumn('d1.ssn_cpn', '=', 'd2.ssn_cpn')
                                ->orWhereColumn('d1.phone_number', '=', 'd2.phone_number');
                        })

                        ->when(($user_uuid!=''), function ($q) use ($user_uuid){
                            $q->where('d1.user_uuid', $user_uuid);
                        })

                        ->where('d1.status', '!=', Config::get('common.status.deleted'))
                        ->where('d1.approved', Config::get('common.status.actived'))
                        ->where('d2.status', '!=', Config::get('common.status.deleted'))
                        ->where('d2.approved', Config::get('common.status.actived'))

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

    public function get($uuid)
    {
        $director = Director::where('uuid', $uuid)
                            ->where('status', '!=', Config::get('common.status.deleted'))
                            ->where('aprroved', Config::get('common.status.actived'))
                            ->first(['uuid', 'first_name', 'middle_name', 'last_name']);
        return $director;
    }

    public function delete(Director $director)
    {
        $director->update(['status' => Config::get('common.status.deleted')]);
        $this->addressService->delete_by_entity($director->uuid);
        $this->emailService->delete_by_entity($director->uuid);
    }

    public function check($entity, $ignore_uuid = '')
    {
 
        $check = [];

        // Ssn
        if (isset($entity['ssn_cpn'])){
            $check['tmp'] = Director::select('ssn_cpn', 'first_name', 'middle_name', 'last_name')
                                        ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                            return $q->where('uuid', '!=', $ignore_uuid);
                                        })
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

        // Phone
        if (isset($entity['phone_number'])){
            $check['tmp'] = Director::select('phone_number', 'first_name', 'middle_name', 'last_name')
                                        ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                            return $q->where('uuid', '!=', $ignore_uuid);
                                        })
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

        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.add')),
            'changes' => json_encode(new DirectorResource($director)),
            'action_code' => Config::get('common.activity.codes.director_add'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        $director['last_activity'] = $this->activityService->by_entity_last($director->uuid);
        return new DirectorPendingResource($director);
    }

    public function update(Director $director, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['approved'] = Config::get('common.status.actived');
        $director->update($entity);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];

        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.update')),
            'changes' => json_encode(new DirectorResource($director)),
            'action_code' => Config::get('common.activity.codes.director_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        $director['last_activity'] = $this->activityService->by_entity_last($director->uuid);
        return new DirectorPendingResource($director);
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $director = Director::create($entity);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];

        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.pending')),
            'changes' => json_encode(new DirectorResource($director)),
            'action_code' => Config::get('common.activity.codes.director_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // telegram
        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $director_fn . "*", Config::get('common.activity.director.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // push pending
        $director['last_activity'] = $this->activityService->by_entity_last($director['uuid']);
        $this->notificationService->push_to_headquarters('pending', ['data' => new DirectorPendingResource($director), 'msg' => '', 'link' => '']);

        return new DirectorResource($director);
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

        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.pending_update')),
            'changes' => json_encode(new DirectorResource($director)) ,
            'action_code' => Config::get('common.activity.codes.director_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $user_uuid)->first();

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // telegram
        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $director_fn . "*", Config::get('common.activity.director.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // push pending
        $director['last_activity'] = $this->activityService->by_entity_last($director['uuid']);
        $this->notificationService->push_to_headquarters('pending', ['data' => new DirectorPendingResource($director), 'msg' => '', 'link' => '']);

        return new DirectorResource($director);
    }

    public function accept(Director $director, $entity, $user_uuid, $override = false)
    {
        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $director->update($entity);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];
        
        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, ($override?Config::get('common.activity.director.override'):Config::get('common.activity.director.accept'))),
            'changes' => json_encode(new DirectorResource($director)),
            'action_code' => Config::get('common.activity.codes.director_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $director['user_uuid'])->first();

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push('activity', $user, ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // telegram
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $director_fn . "*", Config::get('common.activity.director.accept')) . "\n" .
            '[link to view]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')'
        ]);

        // push pending
        $director['last_activity'] = $this->activityService->by_entity_last($director['uuid']);
        $this->notificationService->push('pending', $user, ['data' => new DirectorPendingResource($director), 'msg' => '', 'link' => '']);

        $director['last_activity'] = $this->activityService->by_entity_last($director->uuid);
        return new DirectorPendingResource($director);
    }

    public function reject($uuid, $user_uuid)
    {
        $director = Director::where('uuid', $uuid)->first();
        $director->update(['status' => Config::get('common.status.rejected')]);

        // director full name
        $director_fn = $director['first_name'] . ' ' . ($director['middle_name']!=null?$director['middle_name'].' ':'') . $director['last_name'];

        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $director_fn, Config::get('common.activity.director.reject')),
            'changes' => json_encode(new DirectorResource($director)),
            'action_code' => Config::get('common.activity.codes.director_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $director['user_uuid'])->first();

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push('activity', $user, ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // telegram
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $director_fn . "*", Config::get('common.activity.director.reject')) . "\n" .
                        '[link to change]('.env('APP_FRONTEND_ENDPOINT').'/directors/'.$director['uuid'].')'
        ]);

        // push pending
        $director['last_activity'] = $this->activityService->by_entity_last($director['uuid']);
        $this->notificationService->push('pending', $user, ['data' => new DirectorPendingResource($director), 'msg' => '', 'link' => '']);

        return new DirectorPendingResource($director);
    }

    public function director_list($value = '')
    {
        $directors = Director::orderBy('first_name', 'ASC')
                                ->orderBy('middle_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->orderBy('updated_at', 'DESC')
                                ->where('status', '!=', Config::get('common.status.deleted'))
                                ->where('approved', Config::get('common.status.actived'))
                                ->where(function($q) use ($value){
                                    return $q->whereRaw("concat(first_name, ' ', middle_name, ' ', last_name) like '%".$value."%'")
                                                ->orWhereRaw("concat(last_name, ' ', middle_name, ' ', first_name) like '%".$value."%'")
                                                ->orWhereRaw("concat(first_name, ' ', last_name) like '%".$value."%'")
                                                ->orWhereRaw("concat(last_name, ' ', first_name) like '%".$value."%'");
                                })
                                ->limit(20)
                                ->get(['uuid', 'first_name', 'middle_name', 'last_name']);
        return $directors;
    }

    public function unlink($uuid)
    {
        Company::where('director_uuid', $uuid)
                ->update(['director_uuid' => null]);

        Director::where('uuid', $uuid)
                ->update(['company_association' => null]);

        return true;
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
        return ' on director card ' . strtoupper($entity['first_name']) . ' ' . strtoupper($entity['middle_name']) . ' ' . strtoupper($entity['last_name']);
    }

}