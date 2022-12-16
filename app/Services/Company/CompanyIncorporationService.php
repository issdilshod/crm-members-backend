<?php

namespace App\Services\Company;

use App\Models\Company\CompanyIncorporation;

class CompanyIncorporationService{

    public function save($entity)
    {
        $companyIncorporation = CompanyIncorporation::where('entity_uuid', $entity['entity_uuid'])
                                            ->where(function ($q) use($entity){
                                                $q->where('parent', $entity['parent'])
                                                    ->orWhere('parent', null);
                                            })
                                            ->first();
        if ($companyIncorporation!=null){
            $companyIncorporation->update($entity);
        }else{
            $companyIncorporation = CompanyIncorporation::create($entity);
        }

        return $companyIncorporation;
    }
}