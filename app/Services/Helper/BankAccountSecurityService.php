<?php

namespace App\Services\Helper;

use App\Models\Helper\BankAccountSecurity;
use Illuminate\Support\Facades\Config;

class BankAccountSecurityService{

    public function save($entity)
    {
        if (isset($entity['uuid'])){
            $bankAccountSecurity = BankAccountSecurity::where('uuid', $entity['uuid'])->update($entity);     
        }else{
            $bankAccountSecurity = BankAccountSecurity::create($entity);
        }

        return $bankAccountSecurity;
    }

    public function delete($uuid)
    {
        BankAccountSecurity::where('uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

}