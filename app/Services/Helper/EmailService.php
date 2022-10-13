<?php

namespace App\Services\Helper;

use App\Models\Helper\Address;
use App\Models\Helper\Email;
use Illuminate\Support\Facades\Config;

class EmailService {


    public function create($entity)
    {
        $email = Email::create($entity);
        return $email;
    }

    public function check($entity)
    {
        $check = [];

        if (isset($entity['hosting_uuid']) && isset($entity['email'])){
            $check['tmp'] = Email::select('hosting_uuid', 'email')
                                            ->where('status', Config::get('common.status.actived'))
                                            ->where('hosting_uuid', $entity['hosting_uuid'])
                                            ->where('email', $entity['email'])->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['emails.'.$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        // Phone
        if (isset($entity['phone'])){
            $check['tmp'] = Email::select('phone')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('phone', $entity['phone'])->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['emails.'.$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        return $check;
    }

    public function check_ignore($entity, $ignore_uuid)
    {
        $check = [];

        if (isset($entity['hosting_uuid']) && isset($entity['email'])){
            $check['tmp'] = Email::select('hosting_uuid', 'email')
                                            ->where('entity_uuid', '!=', $ignore_uuid)
                                            ->where('status', Config::get('common.status.actived'))
                                            ->where('hosting_uuid', $entity['hosting_uuid'])
                                            ->where('email', $entity['email'])->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['emails.'.$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        // Phone
        if (isset($entity['phone'])){
            $check['tmp'] = Email::select('phone')
                                    ->where('entity_uuid', '!=', $ignore_uuid)
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('phone', $entity['phone'])->first();
            if ($check['tmp']!=null){
                $check['tmp'] = $check['tmp']->toArray();
                foreach ($check['tmp'] AS $key => $value):
                    $check['emails.'.$key] = Config::get('common.errors.exsist');
                endforeach;
            }
            unset($check['tmp']);
        }

        return $check;
    }

    public function delete_by_entity($uuid)
    {
        Email::where('entity_uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }
}