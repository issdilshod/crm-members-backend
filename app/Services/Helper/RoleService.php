<?php

namespace App\Services\Helper;

use App\Models\Helper\Role;
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