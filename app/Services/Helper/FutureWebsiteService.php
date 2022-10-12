<?php

namespace App\Services\Helper;

use App\Models\Helper\FutureWebsite;
use Illuminate\Support\Facades\Config;

class FutureWebsiteService {

    public function save($entity)
    {
        $futureWebsite = FutureWebsite::where('entity_uuid', $entity['entity_uuid'])
                                        ->where('domain', $entity['domain'])
                                        ->first();
        if ($futureWebsite==null){
            $futureWebsite = FutureWebsite::create($entity);
        }else {
            $futureWebsite->update($entity);
        }

        return $futureWebsite;
    }

    public function delete($uuid)
    {
        FutureWebsite::where('uuid', $uuid)
                        ->update(['status' => Config::get('common.status.deleted')]);
    }

}