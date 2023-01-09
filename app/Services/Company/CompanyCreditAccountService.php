<?php

namespace App\Services\Company;

use App\Models\Company\CompanyCreditAccount;

class CompanyCreditAccountService{

    public function save($entity)
    {
        $companyCreditAccount = CompanyCreditAccount::where('entity_uuid', $entity['entity_uuid'])
                                            ->first();
        if ($companyCreditAccount!=null){
            $companyCreditAccount->update($entity);
        }else{
            $companyCreditAccount = CompanyCreditAccount::create($entity);
        }

        return $companyCreditAccount;
    }

}