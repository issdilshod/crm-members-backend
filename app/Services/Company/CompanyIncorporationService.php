<?php

namespace App\Services\Company;

use App\Services\Helper\AddressService;
use App\Services\Helper\RegisterAgentService;

class CompanyIncorporationService{

    private $registerAgentService;
    private $addressService;

    public function __construct()
    {
        $this->registerAgentService = new RegisterAgentService();
        $this->addressService = new AddressService();
    }

    public function save($entity)
    {
        
    }
}