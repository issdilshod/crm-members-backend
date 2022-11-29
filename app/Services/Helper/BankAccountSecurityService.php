<?php

namespace App\Services\Helper;

use App\Models\Helper\BankAccountSecurity;
use Illuminate\Support\Facades\Config;

class BankAccountSecurityService{

    public function save($entity)
    {
        $bankAccountSecurity = BankAccountSecurity::find($entity);
        if (!$bankAccountSecurity->count()){
            $bankAccountSecurity = BankAccountSecurity::create($entity);
        }else{
            $bankAccountSecurity->update($entity);
        }

        return $bankAccountSecurity;
    }

    public function delete($uuid)
    {
        BankAccountSecurity::where('uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

}