<?php

namespace App\Services\Contact;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Account\ActivityResource;
use App\Http\Resources\Contact\ContactPendingResource;
use App\Http\Resources\Contact\ContactResource;
use App\Models\Account\Activity;
use App\Models\Account\User;
use App\Models\Contact\Contact;
use App\Services\Account\ActivityService;
use App\Services\Helper\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ContactService{

    private $notificationService;
    private $activityService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->activityService = new ActivityService();
    }

    public function all()
    {
        $contacts = Contact::orderBy('updated_at')
                            ->where('status', '!=', Config::get('common.status.deleted'))
                            ->where('approved', Config::get('common.status.actived'))
                            ->paginate(20);

        foreach($contacts AS $key => $value):
            $contacts[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return ContactPendingResource::collection($contacts);
    }

    public function for_pending($user_uuid, $filter, $filter_summary, $filter_by_user)
    {
        $contacts = Contact::from('contacts as c1')
                                        ->select('c1.*')
                                        ->orderBy('c1.updated_at', 'DESC')
                                        ->groupBy('c1.uuid')
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
                                        ->where('c1.status', '!=', Config::get('common.status.deleted'))
                                        ->when(($filter_summary==''), function ($gq) use ($filter) { // no summary filter
                                            return $gq->when(($filter!='' || $filter=='0'), function ($q) { // normal view
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
                                        ->when(($filter_summary!=''), function($q){ // never true
                                            return $q->where('c1.status', 100); // never true
                                        })
                                        ->when(($user_uuid!=''), function ($q) use($user_uuid){
                                            return $q->where('c1.user_uuid', $user_uuid);
                                        })
                                        ->where('c1.status', '!=', Config::get('common.status.deleted'))
                                        ->paginate(5);

        foreach($contacts AS $key => $value):
            $contacts[$key]['last_activity'] = $this->activityService->by_entity_last($value['uuid']);
        endforeach;

        return ContactPendingResource::collection($contacts);
    }

    public function one(Contact $contact)
    {
        $contact = new ContactResource($contact);
        return $contact;
    }

    public function create($entity)
    {
        $entity['approved'] = Config::get('common.status.actived');
        $contact = Contact::create($entity);

        // Activity log
        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $contact['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $contact['first_name'] . ' ' . $contact['last_name'], Config::get('common.activity.contact.add')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.contact_add'),
            'status' => Config::get('common.status.actived')
        ]);

        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        return new ContactPendingResource($contact);
    }

    public function update(Contact $contact, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['approved'] = Config::get('common.status.actived');
        $contact->update($entity);

        Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $contact['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $contact['first_name'] . ' ' . $contact['last_name'], Config::get('common.activity.contact.updated')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.contact_update'),
            'status' => Config::get('common.status.actived')
        ]);

        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        return new ContactPendingResource($contact);
    }

    public function delete(Contact $contact)
    {
        $contact->update(['status' => Config::get('common.status.deleted')]);
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $contact = Contact::create($entity);

        $name = $contact['first_name'] . ' ' . $contact['last_name'];

        // Activity log
        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $contact['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.contact.pending')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.contact_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $entity['user_uuid'])->first();

        // telegram
        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $name . "*", Config::get('common.activity.contact.pending')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/contacts/'.$contact['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // push pending
        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        $this->notificationService->push_to_headquarters('pending', ['data' => new ContactPendingResource($contact), 'msg' => '', 'link' => '']);

        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        return new ContactPendingResource($contact);
    }

    public function pending_update(Contact $contact, $entity, $user_uuid)
    {
        $entity['updated_at'] = Carbon::now();
        $entity['status'] = Config::get('common.status.pending');
        $contact->update($entity);

        // get name
        $name = $contact['first_name'] . ' ' . $contact['last_name'];

        // logs
        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $contact['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.contact.pending_update')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.contact_pending_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $contact['user_uuid'])->first();

        // telegram
        $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                str_replace("{name}", "*" . $name . "*", Config::get('common.activity.contact.pending_update')) . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/contacts/'.$contact['uuid'].')';
        $this->notificationService->telegram_to_headqurters($msg);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // push pending
        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        $this->notificationService->push_to_headquarters('pending', ['data' => new ContactPendingResource($contact), 'msg' => '', 'link' => '']);

        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        return new ContactPendingResource($contact);
    }

    public function accept(Contact $contact, $entity, $user_uuid)
    {
        $entity['status'] = Config::get('common.status.actived');
        $entity['approved'] = Config::get('common.status.actived');
        $contact->update($entity);

        // get name
        $name = $contact['first_name'] . ' ' . $contact['last_name'];

        // log
        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $contact['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.contact.accept')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.contact_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $contact['user_uuid'])->first();

        // telegram
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $name . "*", Config::get('common.activity.contact.accept')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/contacts/'.$contact['uuid']. ')'
        ]);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push('activity', $user, ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // push pending
        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        $this->notificationService->push('pending', $user, ['data' => new ContactPendingResource($contact), 'msg' => '', 'link' => '']);

        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        return new ContactPendingResource($contact);
    }

    public function reject($uuid, $user_uuid)
    {
        $contact = Contact::where('uuid', $uuid)->first();

        $entity['status'] = Config::get('common.status.rejected');
        $contact->update($entity);

        // get name
        $name = $contact['first_name'] . ' ' . $contact['last_name'];

        // log
        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $contact['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => str_replace("{name}", $name, Config::get('common.activity.contact.reject')),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.contact_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $user = User::where('uuid', $contact['user_uuid'])->first();

        // telegram
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => str_replace("{name}", "*" . $name . "*", Config::get('common.activity.contact.reject')) . "\n" .
                        '[link to view](' .env('APP_FRONTEND_ENDPOINT').'/vitual-offices/'.$contact['uuid']. ')'
        ]);

        // push activity
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push('activity', $user, ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        // push pending
        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        $this->notificationService->push('pending', $user, ['data' => new ContactPendingResource($contact), 'msg' => '', 'link' => '']);

        $contact['last_activity'] = $this->activityService->by_entity_last($contact['uuid']);
        return new ContactPendingResource($contact);
    }

}