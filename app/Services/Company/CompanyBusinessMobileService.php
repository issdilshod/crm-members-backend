<?php

namespace App\Services\Company;

use App\Models\Company\CompanyBusinessMobile;
use Illuminate\Support\Facades\Config;

class CompanyBusinessMobileService{

    public function save($entity)
    {
        $companyBusinessMobile = CompanyBusinessMobile::where('entity_uuid', $entity['entity_uuid'])
                                                    ->where(function ($q) use($entity){
                                                        $q->where('parent', $entity['parent'])
                                                            ->orWhere('parent', null);
                                                    })
                                                    ->first();
        if ($companyBusinessMobile!=null){
            $companyBusinessMobile->update($entity);
        }else{
            $companyBusinessMobile = CompanyBusinessMobile::create($entity);
        }

        return $companyBusinessMobile;
    }

    public function delete($uuid)
    {
        CompanyBusinessMobile::where('uuid', $uuid)
                            ->update(['status' => Config::get('common.status.deleted')]);
    }

}