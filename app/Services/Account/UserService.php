<?php

namespace App\Services\Account;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Account\UserResource;
use App\Models\Account\Activity;
use App\Models\Account\InviteUser;
use App\Models\Account\User;
use App\Services\Helper\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class UserService {

    private $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function all()
    {
        $users = User::where('status', Config::get('common.status.actived'))
                        ->paginate(100);
        return UserResource::collection($users);
    }

    public function one(User $user)
    {
        $user = new UserResource($user);
        return $user;
    }

    public function delete(User $user)
    {
        $user->update(['status' => Config::get('common.status.deleted')]);
    }

    public function pendings()
    {
        $users = User::where('status', Config::get('common.status.pending'))
                        ->paginate(100);
        return UserResource::collection($users);
    }

    public function me(Request $request)
    {
        $validated = $request->validate([
            'user_uuid' => 'string'
        ]);

        $user = User::where('uuid', $validated['user_uuid'])
                        ->first();
        return new UserResource($user);
    }

    public function register($entity)
    {
        $invite_user = InviteUser::select('uuid', 'via', 'unique_identify')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('entry_token', $entity['entry_token'])
                                    ->first();
        
        if ($invite_user!=null){

            #region Check

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

            #endregion
            
            $entity['status'] = Config::get('common.status.pending');
            $user = User::create($entity);

            $invite_user->update(['status'=> Config::get('common.status.deleted')]);

            // activity
            Activity::create([
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
            
            return response()->json([
                'data' => ['msg' => 'Succesfully sent request for register.'],
            ], 200);
        }

        return response()->json([
            'data' => ['msg' => 'Invalid token!'],
        ], 404);
    }

}