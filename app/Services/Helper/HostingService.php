<?php

namespace App\Services\Helper;

use App\Models\Helper\Hosting;
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