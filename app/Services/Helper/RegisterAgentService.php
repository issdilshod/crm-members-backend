<?php

namespace App\Services\Helper;

use App\Models\Helper\RegisterAgent;

class RegisterAgentService{

    public function save($entity)
    {

        $registerAgent = RegisterAgent::where('entity_uuid', $entity['entity_uuid'])
                                        ->where(function ($q) use($entity){
                                            $q->where('parent', $entity['parent'])
                                                ->orWhere('parent', null);
                                        })
                                        ->first();
        if ($registerAgent!=null){
            $registerAgent->update($entity);
        }else{
            $registerAgent = RegisterAgent::create($entity);
        }

        return $registerAgent;
    }

}