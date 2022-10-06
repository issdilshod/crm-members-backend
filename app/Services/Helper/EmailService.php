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

        $check['hosting_email'] = Email::select('hosting_uuid', 'email')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('hosting_uuid', $entity['hosting_uuid'])
                                        ->where('email', $entity['email'])->first();
        if ($check['hosting_email']!=null){
            $check['hosting_email'] = $check['hosting_email']->toArray();
            foreach ($check['hosting_email'] AS $key => $value):
                $check['emails.'.$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['hosting_email']);

        // Phone
        $check['phone'] = Email::select('phone')
                                    ->where('status', Config::get('common.status.actived'))
                                    ->where('phone', $entity['phone'])->first();
        if ($check['phone']!=null){
            $check['phone'] = $check['phone']->toArray();
            foreach ($check['phone'] AS $key => $value):
                $check['emails.'.$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['phone']);

        return $check;
    }

    public function delete_by_entity($uuid)
    {
        Email::where('entity_uuid', $uuid)->update('status', Config::get('common.status.deleted'));
    }
}