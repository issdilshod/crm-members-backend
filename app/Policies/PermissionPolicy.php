<?php

namespace App\Policies;

use App\Models\Account\Permission;
use App\Models\Account\RolePermission;
use App\Models\Account\User;
use App\Models\Account\UserPermission;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Config;

class PermissionPolicy
{
    use HandlesAuthorization;

    public static function permission($user_uuid, $permission_alias = '')
    {
        // get user
        $user = User::join('roles', 'users.role_uuid', '=', 'roles.uuid')
                        ->where('users.uuid', $user_uuid)
                        ->where('users.status', Config::get('common.status.actived'))
                        ->first(['roles.uuid', 'roles.alias']);

        // if headquarters then exists permission
        if ($user->alias === Config::get('common.role.headquarters')){
            return true;
        }

        // if not on permission list and not headquarters
        if ($permission_alias==''){
            return false;
        }

        // get permission uuid from alias
        $permission = Permission::where('status', Config::get('common.status.actived'))
                                    ->where('alias', $permission_alias)
                                    ->first(['uuid']);

        // check permission of user
        $has_permission = UserPermission::where('permission_uuid', $permission->uuid)
                                            ->where('user_uuid', $user_uuid)
                                            ->first();
        if ($has_permission!=null){
            if ($has_permission->status==Config::get('common.status.actived')){
                return true;
            }else{
                return false;
            }
        }

        // check permission of role
        $has_permission = RolePermission::where('status', Config::get('common.status.actived'))
                                            ->where('permission_uuid', $permission->uuid)
                                            ->where('role_uuid', $user->uuid) // NOTO: uuid is role uuid, look to first query
                                            ->first();
        if ($has_permission!=null){
            return true;
        }

        return false;

    }
}
