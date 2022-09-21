<?php

namespace App\Services;

use App\Models\API\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class UserService {

    /**
     * Return 100 last added user
     * 
     * @return User
     */
    public function getUsers()
    {
        $users = User::where('status', Config::get('common.status.actived'))
                        ->paginate(100);
        return $users;
    }

    /**
     * Delete user
     * 
     * @return void
     */
    public function deleteUser(User $user)
    {
        $user->update(['status' => Config::get('common.status.deleted')]);
    }

    /**
     * Return pending users
     * 
     * @return User
     */
    public function getPendingUsers()
    {
        $users = User::where('status', Config::get('common.status.pending'))
                        ->paginate(100);
        return $users;
    }

    /**
     * Return Me
     * 
     * @return User
     */
    public function getMe(Request $request)
    {
        $validated = $request->validate([
            'user_uuid' => 'string'
        ]);
        $user = User::where('uuid', $validated['user_uuid'])
                        ->first();
        return $user;
    }

}