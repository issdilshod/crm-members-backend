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

    public function check($entity, $index, $ignore_uuid = '')
    {
        $check = [];

        if (isset($entity['street_address']) && isset($entity['address_line_2']) && isset($entity['city']) && isset($entity['postal'])){
            $check['tmp'] = Address::select('entity_uuid', 'street_address', 'address_line_2', 'city', 'postal', 'address_parent')
                                    ->when(($ignore_uuid!=''), function ($q) use ($ignore_uuid){
                                        return $q->where('entity_uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where(function($q) use ($entity){
                                        $q->where('street_address', $entity['street_address'])
                                            ->where('address_line_2', $entity['address_line_2'])
                                            ->where('city', $entity['city'])
                                            ->where('postal', $entity['postal']);
                                    })
                                    ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['addresses'.'.'.$entity['address_parent'].'.'.$key] = Config::get('common.errors.exsist') . $this->get_identifier_exists($check['tmp']['entity_uuid']);
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
            $message = ' on director card ' . strtoupper($director['first_name']) . ' ' . strtoupper($director['middle_name']) . ' ' . strtoupper($director['last_name']);
        }else if ($company!=null){
            $message = ' on company card ' . strtoupper($company['legal_name']);
        }
        return $message;
    }
}