<?php

namespace App\Services\Helper;

use App\Models\Helper\AccountSecurity;
use Illuminate\Support\Facades\Config;

class AccountSecurityService{

    public function save($entity)
    {
        if (isset($entity['uuid'])){
            $accountSecurity = AccountSecurity::where('uuid', $entity['uuid'])->update($entity);     
        }else{
            $accountSecurity = AccountSecurity::create($entity);
        }

        return $accountSecurity;
    }

    public function delete($uuid)
    {
        AccountSecurity::where('uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

}