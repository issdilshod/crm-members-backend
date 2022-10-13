<?php

namespace App\Services\Helper;

use App\Logs\TelegramLog;
use App\Models\Helper\Address;
use Illuminate\Support\Facades\Config;

class AddressService {

    public function create($entity)
    {
        $address = Address::create($entity);
        return $address;
    }

    public function check($entity, $key_parent = '')
    {
        $check = [];

        if (isset($entity['street_address']) && isset($entity['address_line_2']) && isset($entity['city']) && isset($entity['postal'])){
            $check['tmp'] = Address::select('street_address', 'address_line_2', 'city', 'postal')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where(function($query) use ($entity){
                                            $query->where('street_address', $entity['street_address'])
                                                    ->where('address_line_2', $entity['address_line_2'])
                                                    ->where('city', $entity['city'])
                                                    ->where('postal', $entity['postal']);
                                    })->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['address.'.($key_parent!=''?$key_parent.'.':'').$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        return $check;
    }

    public function check_ignore($entity, $ingore_uuid, $key_parent = '')
    {
        $check = [];

        if (isset($entity['street_address']) && isset($entity['address_line_2']) && isset($entity['city']) && isset($entity['postal'])){
            $check['tmp'] = Address::select('street_address', 'address_line_2', 'city', 'postal')
                                        ->where('entity_uuid', '!=', $ingore_uuid)
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where(function($query) use ($entity){
                                                $query->where('street_address', $entity['street_address'])
                                                        ->where('address_line_2', $entity['address_line_2'])
                                                        ->where('city', $entity['city'])
                                                        ->where('postal', $entity['postal']);
                                        })->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['address.'.($key_parent!=''?$key_parent.'.':'').$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        return $check;
    }

    public function delete_by_entity($uuid)
    {
        Address::where('entity_uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }
}