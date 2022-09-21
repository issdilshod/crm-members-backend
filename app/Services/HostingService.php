<?php

namespace App\Services;

use App\Models\API\Hosting;
use Illuminate\Support\Facades\Config;

class HostingService {

    /**
     * Return list of hostings
     * 
     * @return Hosting
     */
    public function getHostings()
    {
        $hostings = Hosting::all()
                                ->where('status', Config::get('common.status.actived'));
        return $hostings;
    }
}