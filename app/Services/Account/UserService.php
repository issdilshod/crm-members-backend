<?php

namespace App\Services\Account;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Account\ActivityResource;
use App\Http\Resources\Account\UserResource;
use App\Models\Account\Activity;
use App\Models\Account\InviteUser;
use App\Models\Account\User;
use App\Models\Account\UserAccessToken;
use App\Services\Helper\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class UserService {

    private $notificationService;
    private $activityService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->activityService = new ActivityService();
    }

    public function all()
    {
        $users = User::orderByRaw('last_seen is not null, last_seen DESC')
                        ->orderBy('first_name', 'ASC')
                        ->orderBy('last_name', 'ASC')
                        ->where('status', Config::get('common.status.actived'))
                        ->paginate(100);
        return UserResource::collection($users);
    }

    public function one(User $user)
    {
        $user = new UserResource($user);
        return $user;
    }

    public function pendings()
    {
        $users = User::where('status', Config::get('common.status.pending'))
                        ->paginate(100);
        return UserResource::collection($users);
    }

    public function me($user_uuid)
    {
        $user = User::select('users.uuid', 'roles.alias as role_alias')
                    ->join('roles', 'roles.uuid', '=', 'users.role_uuid')
                    ->where('users.uuid', $user_uuid)
                    ->first();
        return $user;
    }

    public function check($entity)
    {
        $check = [];

        // username
        $check['tmp'] = User::select('username')
                        ->where('status', Config::get('common.status.actived'))
                        ->where('username', $entity['username'])
                        ->first();

        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] as $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // telegram
        $check['tmp'] = User::select('telegram')
                            ->where('status', Config::get('common.status.actived'))
                            ->where('telegram', $entity['telegram'])
                            ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] as $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        return $check;
    }

    public function check_ignore($entity, $ignore_uuid)
    {
        $check = [];

        // username
        $check['tmp'] = User::select('username')
                        ->where('uuid', '!=', $ignore_uuid)
                        ->where('status', Config::get('common.status.actived'))
                        ->where('username', $entity['username'])
                        ->first();

        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] as $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        // telegram
        $check['tmp'] = User::select('telegram')
                            ->where('uuid', '!=', $ignore_uuid)
                            ->where('status', Config::get('common.status.actived'))
                            ->where('telegram', $entity['telegram'])
                            ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] as $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        return $check;
    }

    public function create($entity)
    {
        $user = User::create($entity);

        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $user['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.user.add'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.user_add'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        return $user;
    }

    public function update(User $user, $entity)
    {
        $user->update($entity);

        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $user->uuid,
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.user.update'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.user_update'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        return $user;
    }

    public function accept(User $user, $entity)
    {
        $entity['status'] = Config::get('common.status.actived');
        $user->update($entity);

        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $user['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.user.accept'),
            'changes' => '',
            'action_code' => Config::get('common.activity.codes.user_accept'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => Config::get('common.activity.user.accept') . '! ' . 
                    'This is your link for [login]('.env('APP_FRONTEND_ENDPOINT'). '/login/' .')' 
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        return $user;
    }

    public function reject($uuid, $user_uuid)
    {
        $user = User::where('uuid', $uuid)->first();
        $user->update(['status' => Config::get('common.status.deleted')]);

        $activity = Activity::create([
            'user_uuid' => $user_uuid,
            'entity_uuid' => $user['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.user.reject'),
            'changes' => '',
            'action_code' => Config::get('common.activity.codes.user_reject'),
            'status' => Config::get('common.status.actived')
        ]);

        // notification
        $this->notificationService->telegram([
            'telegram' => $user['telegram'],
            'msg' => Config::get('common.activity.user.reject')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
    }

    public function login($entity)
    {
        $user = User::where('username', $entity['username'])
                        ->where('password', $entity['password'])
                        ->where('status', Config::get('common.status.actived'))
                        ->first();

        if (!$user){ // invalid
            return response()->json([
                'data' => ['msg' => Config::get('common.errors.invalid_login')],
            ], 404);
        }

        $token = Str::random(32);
        $expires_at = Carbon::now();
        $expires_at = $expires_at->addDays(Config::get('common.session.token_deadline'))->toDateTimeString(); // after CONFIG day expires

        $user['access_token'] = ['user_uuid' => $user['uuid'], 'token' => $token, 'expires_at' => $expires_at];

        UserAccessToken::create($user['access_token']);

        $this->online($user->uuid);

        $activity = Activity::create([
            'user_uuid' => $user['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' =>  UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.logged'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);

        return $user;
    }

    public function logout($entity)
    {
        UserAccessToken::where('token', $entity['token'])
                        ->update(['status' => Config::get('common.status.deleted')]);

        $this->offline($entity['user_uuid']);

        $activity = Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.logout'),
            'status' => Config::get('common.status.actived')
        ]);

        // push
        $activity = $this->activityService->setLink($activity);
        $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
    }

    public function register($entity)
    {
        $invite_user = InviteUser::select('uuid', 'via', 'unique_identify')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('entry_token', $entity['entry_token'])
                                    ->first();
        
        if ($invite_user!=null){

            $user = User::select('username')
                            ->where('status', Config::get('common.status.actived'))
                            ->where('username', $entity['username'])
                            ->first();

            $check = [];
 
            if ($user!=null){
                $check['tmp'] = $user->toArray();
                foreach ($check['tmp'] as $key => $value):
                    $check[$key] = Config::get('common.errors.exsist');
                endforeach;
                unset($check['tmp']);
                
            }

            $user = User::select('telegram')
                            ->where('status', Config::get('common.status.actived'))
                            ->where('telegram', $entity['telegram'])
                            ->first();
                   
            if ($user!=null){
                $check['tmp'] = $user->toArray();
                foreach ($check['tmp'] as $key => $value):
                    $check[$key] = Config::get('common.errors.exsist');
                endforeach;
                unset($check['tmp']);
            }
            
            if (count($check)>0){
                return response()->json([
                    'data' => ['msg' => 'Username is exsist.', 'data' => $check],
                ], 409);
            }
            
            $entity['status'] = Config::get('common.status.pending');
            $user = User::create($entity);
            $this->offline($user->uuid);

            $invite_user->update(['status'=> Config::get('common.status.deleted')]);

            $activity = Activity::create([
                'user_uuid' => $user['uuid'],
                'entity_uuid' => $user['uuid'],
                'device' => UserSystemInfoHelper::device_full(),
                'ip' => UserSystemInfoHelper::ip(),
                'description' => Config::get('common.activity.user.register'),
                'changes' => json_encode($user),
                'action_code' => Config::get('common.activity.codes.user_register'),
                'status' => Config::get('common.status.actived')
            ]);

            // notification
            $msg = '*' . $user->first_name . ' ' . $user->last_name . "*\n" .
                Config::get('common.activity.user.register') . "\n" .
                '[link to approve]('.env('APP_FRONTEND_ENDPOINT').'/departments/user/'.$user['uuid'].')';
            $this->notificationService->telegram_to_headqurters($msg);

            // push
            $activity = $this->activityService->setLink($activity);
            $this->notificationService->push_to_headquarters('activity', ['data' => new ActivityResource($activity), 'msg' => '', 'link' => '']);
            
            return response()->json([
                'data' => ['msg' => 'Succesfully sent request for register.'],
            ], 200);
        }

        return response()->json([
            'data' => ['msg' => 'Invalid token!'],
        ], 404);
    }

    public function delete(User $user)
    {
        $user->update(['status' => Config::get('common.status.deleted')]);

        UserAccessToken::where('user_uuid', $user->uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

    public function online($uuid)
    {
        User::where('uuid', $uuid)
                ->update(['last_seen' => NULL]);
    }

    public function offline($uuid)
    {
        User::where('uuid', $uuid)
                ->update(['last_seen' => Carbon::now()]);
    }

}