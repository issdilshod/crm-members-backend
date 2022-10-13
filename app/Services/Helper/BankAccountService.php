<?php
namespace App\Services\Helper;

use App\Models\Helper\BankAccount;
use Illuminate\Support\Facades\Config;

class BankAccountService {

    public function save($entity)
    {
        $bankAccount = BankAccount::where('entity_uuid', $entity['entity_uuid'])->first();
        if ($bankAccount!=null){
            $bankAccount->update($entity);
        }else{
            $bankAccount = BankAccount::create($entity);
        }
        return $bankAccount;
    }

    public function check($entity)
    {
        $check = [];

        if (isset($entity['name']) && isset($entity['username']) && isset($entity['account_number']) && isset($entity['routing_number'])){
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
        }

        return $check;
    }

    public function check_ignore($entity, $ingore_uuid)
    {
        $check = [];

        if (isset($entity['name']) && isset($entity['username']) && isset($entity['account_number']) && isset($entity['routing_number'])){
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
        }

        return $check;
    }

    public function delete_by_entity($uuid)
    {
        BankAccount::where('entity_uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

}