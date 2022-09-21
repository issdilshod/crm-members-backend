<?php

namespace App\Services;

use App\Models\API\Role;
use Illuminate\Support\Facades\Config;

class RoleService {

    /**
     * Return list of roles
     * 
     * @return Role
     */
    public function getRoles()
    {
        $roles = Role::orderBy('sort', 'ASC')
                        ->where('status', Config::get('common.status.actived'))
                        ->get();
        return $roles;
    }

}