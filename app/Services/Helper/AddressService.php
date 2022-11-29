<?php

namespace App\Services\Helper;

use App\Models\Company\Company;
use App\Models\Director\Director;
use App\Models\Helper\Address;
use Illuminate\Support\Facades\Config;

class AddressService {

    public function create($entity)
    {
        $address = Address::create($entity);
        return $address;
    }

    public function save($entity)
    {
        $address = Address::where('entity_uuid', $entity['entity_uuid'])
                            ->where(function ($q) use($entity) {
                                $q->where('address_parent', $entity['address_parent'])
                                    ->orWhere('address_parent', null);
                            })
                            ->first();
        if ($address!=null){
            $address->update($entity);
        }else{
            $address = Address::create($entity);
        }

        return $address;
    }

    private function get_identifier_exists($uuid)
    {
        $director = Director::select('first_name', 'middle_name', 'last_name')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('uuid', $uuid)
                                    ->first();
        $company = Company::select('legal_name')
                            ->where('status', Config::get('common.status.actived'))
                            ->where('uuid', $uuid)
                            ->first();
        $message = '';
        if ($director!=null){
            $message = ' On director card *' . $director['first_name'] . ' ' . $director['middle_name'] . ' ' . $director['last_name'] . '*';
        }else if ($company!=null){
            $message = ' On company card *' . $company['legal_name'] . '*';
        }
        return $message;
    }

    public function check($entity, $key_parent = '', $extra = 'address')
    {
        $check = [];

        if (isset($entity['street_address']) && isset($entity['address_line_2']) && isset($entity['city']) && isset($entity['postal'])){
            $check['tmp'] = Address::select('entity_uuid', 'street_address', 'address_line_2', 'city', 'postal')
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
                    $check[$extra.'.'.($key_parent!=''?$key_parent.'.':'').$key] = Config::get('common.errors.exsist') . $this->get_identifier_exists($check['tmp']['entity_uuid']);
                endforeach;
            }
            unset($check['tmp']);
        }

        return $check;
    }

    public function check_ignore($entity, $ingore_uuid, $key_parent = '', $extra = 'address')
    {
        $check = [];

        if (isset($entity['street_address']) && isset($entity['address_line_2']) && isset($entity['city']) && isset($entity['postal'])){
            $check['tmp'] = Address::select('entity_uuid', 'street_address', 'address_line_2', 'city', 'postal')
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
                    $check[$extra.'.'.($key_parent!=''?$key_parent.'.':'').$key] = Config::get('common.errors.exsist') . $this->get_identifier_exists($check['tmp']['entity_uuid']);
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

    public function delete($uuid)
    {
        Address::where('uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }
}