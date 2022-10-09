<?php
namespace App\Services\Helper;

use App\Models\Helper\BankAccount;
use Illuminate\Support\Facades\Config;

class BankAccountService {

    public function create($entity)
    {
        $bankAccount = BankAccount::create($entity);
        return $bankAccount;
    }

    public function check($entity)
    {
        $check = [];

        $check['tmp'] = BankAccount::select('name', 'username', 'account_number', 'routing_number')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('name', $entity['name'])
                                        ->where('username', $entity['username'])
                                        ->where('account_number', $entity['account_number'])
                                        ->where('routing_number', $entity['routing_number'])
                                        ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check['bank_account.'.$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        return $check;
    }

    public function check_ignore($entity, $ingore_uuid)
    {
        $check = [];

        $check['tmp'] = BankAccount::select('name', 'username', 'account_number', 'routing_number')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('entity_uuid', '!=', $ingore_uuid)
                                        ->where('name', $entity['name'])
                                        ->where('username', $entity['username'])
                                        ->where('account_number', $entity['account_number'])
                                        ->where('routing_number', $entity['routing_number'])
                                        ->first();
        if ($check['tmp']!=null){
            $check['tmp'] = $check['tmp']->toArray();
            foreach ($check['tmp'] AS $key => $value):
                $check['bank_account.'.$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['tmp']);

        return $check;
    }

    public function delete_by_entity($uuid)
    {
        BankAccount::where('entity_uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

}