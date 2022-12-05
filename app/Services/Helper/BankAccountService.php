<?php
namespace App\Services\Helper;

use App\Models\Company\Company;
use App\Models\Helper\BankAccount;
use Illuminate\Support\Facades\Config;

class BankAccountService {

    private $bankAccountSecurity;

    public function __construct()
    {
        $this->bankAccountSecurity = new BankAccountSecurityService();
    }

    public function save($entity)
    {
        $bankAccount = BankAccount::where('entity_uuid', $entity['entity_uuid'])->first();
        if ($bankAccount!=null){
            $bankAccount->update($entity);
        }else{
            $bankAccount = BankAccount::create($entity);
        }

        // security
        if (isset($entity['bank_account_security'])){
            foreach ($entity['bank_account_security'] AS $key => $value):
                $value['entity_uuid'] = $bankAccount->uuid;
                $this->bankAccountSecurity->save($value);
            endforeach;
        }

        // security delete
        if (isset($entity['bank_account_security_to_delete'])){
            foreach ($entity['bank_account_security_to_delete'] AS $key => $value):
                $this->bankAccountSecurity->delete($value);
            endforeach;
        }

        return $bankAccount;
    }

    public function check($entity, $ignore_uuid = '')
    {
        $check = [];

        if (isset($entity['name']) && isset($entity['username']) && isset($entity['account_number']) && isset($entity['routing_number'])){
            $check['tmp'] = BankAccount::select('entity_uuid', 'name', 'username', 'account_number', 'routing_number')
                                            ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                                return $q->where('entity_uuid', '!=', $ignore_uuid);
                                            })
                                            ->where('status', Config::get('common.status.actived'))
                                            ->where('name', $entity['name'])
                                            ->where('username', $entity['username'])
                                            ->where('account_number', $entity['account_number'])
                                            ->where('routing_number', $entity['routing_number'])
                                            ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['bank_account.'.$key] = Config::get('common.errors.exsist') . $this->get_identifier_exists($check['tmp']['entity_uuid']);
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

    private function get_identifier_exists($uuid)
    {
        $company = Company::select('legal_name')
                            ->where('status', Config::get('common.status.actived'))
                            ->where('uuid', $uuid)
                            ->first();
        $message = '';
        if ($company!=null){
            $message = ' on company card ' . strtoupper($company['legal_name']);
        }
        return $message;
    }

}