<?php

namespace App\Services\Director;

use App\Helpers\UserSystemInfoHelper;
use App\Http\Resources\Director\DirectorResource;
use App\Models\Account\Activity;
use App\Models\Director\Director;
use App\Services\Helper\AddressService;
use App\Services\Helper\EmailService;
use Illuminate\Support\Facades\Config;

class DirectorService {

    private $addressService;
    private $emailService;

    public function __construct()
    {
        $this->addressService = new AddressService();
        $this->emailService = new EmailService();
    }


    public function all()
    {
        $directors = Director::orderBy('created_at', 'DESC')
                            ->where('status', Config::get('common.status.actived'))
                            ->paginate(20);
        return DirectorResource::collection($directors);
    }

    public function one(Director $director)
    {
        $director = new DirectorResource($director);
        return $director;
    }

    public function delete(Director $director)
    {
        $director->update(['status' => Config::get('common.status.deleted')]);
        $this->addressService->delete_by_entity($director->uuid);
        $this->emailService->delete_by_entity($director->uuid);
    }

    public function search($value)
    {
        $directors = Director::orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->whereRaw("concat(first_name, ' ', last_name) like '%".$value."%'")
                                ->paginate(20);
        return DirectorResource::collection($directors);
    }

    public function check($entity, $type = 'c')
    {

        $check = [];

        // Names
        $check['names'] = Director::select('first_name', 'middle_name', 'last_name')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('first_name', $entity['first_name'])
                                        ->where('middle_name', (isset($entity['middle_name'])?$entity['middle_name']:''))
                                        ->where('last_name', $entity['last_name'])
                                        ->first();
        if ($check['names']!=null){
            $check['names'] = $check['names']->toArray();
            foreach ($check['names'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['names']);

        // Ssn
        $check['ssn'] = Director::select('ssn_cpn')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('ssn_cpn', $entity['ssn_cpn'])
                                        ->first();
        if ($check['ssn']!=null){
            $check['ssn'] = $check['ssn']->toArray();
            foreach ($check['ssn'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['ssn']);

        // Company 
        $check['company_association'] = Director::select('company_association')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('company_association', $entity['company_association'])
                                        ->first();
        if ($check['company_association']!=null){
            $check['company_association'] = $check['company_association']->toArray();
            foreach ($check['company_association'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['company_association']);

        // Phone
        $check['phone_number'] = Director::select('phone_number')
                                        ->where('status', Config::get('common.status.actived'))
                                        ->where('phone_number', $entity['phone_number'])
                                        ->first();
        if ($check['phone_number']!=null){
            $check['phone_number'] = $check['phone_number']->toArray();
            foreach ($check['phone_number'] AS $key => $value):
                $check[$key] = Config::get('common.errors.exsist');
            endforeach;
        }
        unset($check['phone_number']);

        return $check;
    }

    public function create($entity)
    {
        $director = Director::create($entity);

        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.add'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_add'),
            'status' => Config::get('common.status.actived')
        ]);

        return $director;
    }

    public function update(Director $director, $entity)
    {
        $director->update($entity);
        return $director;
    }

    public function pending($entity)
    {
        $entity['status'] = Config::get('common.status.pending');
        $director = Director::create($entity);

        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $director['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.director.pending'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.director_pending'),
            'status' => Config::get('common.status.actived')
        ]);

        return $director;
    }

    public function accept($entity)
    {
        //
    }

    public function reject($entity)
    {
        //
    }

}