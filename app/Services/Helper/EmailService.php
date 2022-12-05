<?php

namespace App\Services\Helper;

use App\Models\Company\Company;
use App\Models\Director\Director;
use App\Models\Helper\Email;
use Illuminate\Support\Facades\Config;

class EmailService {


    public function create($entity)
    {
        $email = Email::create($entity);
        return $email;
    }

    public function save($entity)
    {
        if (isset($entity['uuid']) && $entity['uuid']!=''){
            $email = Email::where('uuid', $entity['uuid'])->first();
        }else{
            $email = Email::where('entity_uuid', $entity['entity_uuid'])
                            ->where('email', $entity['email'])
                            ->first();
        }

        unset($entity['uuid']);

        if ($email==null){
            $email = Email::create($entity);
        }else {
            $email->update($entity);
        }

        return $email;
    }

    public function delete($uuid)
    {
        Email::where('uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

    public function delete_by_entity($uuid)
    {
        Email::where('entity_uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

    public function check($entity, $index, $ignore_uuid = '')
    {
        $check = [];

        if (isset($entity['hosting_uuid']) && isset($entity['email'])){
            $check['tmp'] = Email::select('entity_uuid', 'hosting_uuid', 'email')
                                    ->when(($ignore_uuid!=''), function ($q) use($ignore_uuid){
                                        return $q->where('entity_uuid', '!=', $ignore_uuid);
                                    })
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('hosting_uuid', $entity['hosting_uuid'])
                                    ->where('email', $entity['email'])
                                    ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['emails.'.$index.'.'.$key] = Config::get('common.errors.exsist') . $this->get_identifier_exists($check['tmp']['entity_uuid']);
                endforeach;
            }

            unset($check['tmp']);
        }

        // Phone
        if (isset($entity['phone'])){
            $check['tmp'] = Email::select('entity_uuid', 'phone')
                                ->when(($ignore_uuid!=''), function ($q) use($ignore_uuid){
                                    return $q->where('entity_uuid', '!=', $ignore_uuid);
                                })
                                ->where('status', Config::get('common.status.actived'))
                                ->where('phone', $entity['phone'])
                                ->first();

            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['emails.'.$index.'.'.$key] = Config::get('common.errors.exsist') . $this->get_identifier_exists($check['tmp']['entity_uuid']);
                endforeach;
            }

            unset($check['tmp']);
        }

        return $check;
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