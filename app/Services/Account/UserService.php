<?php

namespace App\Services\Account;

use App\Http\Resources\Account\UserResource;
use App\Models\Account\InviteUser;
use App\Models\Account\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class UserService {

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
            $user = User::select('username')
                            ->where('status', Config::get('common.status.actived'))
                            ->where('username', $entity['username'])
                            ->first();
 
            if ($user!=null){
                $check = [];
                $check['user'] = $user->toArray();
                foreach ($check['user'] as $key => $value):
                    $check[$key] = '~Exsist';
                endforeach;
                unset($check['user']);
                return response()->json([
                    'data' => ['msg' => 'Username is exsist.', 'data' => $check],
                ], 409);
            }

            $entity['status'] = Config::get('common.status.pending');
            User::create($entity);

            $invite_user->update(['status'=> Config::get('common.status.deleted')]);

            // TODO: Send notification to headquaters
            
            return response()->json([
                'data' => ['msg' => 'Succesfully sent request for register.'],
            ], 200);
        }

        return response()->json([
            'data' => ['msg' => 'Invalid token!'],
        ], 404);
    }

}