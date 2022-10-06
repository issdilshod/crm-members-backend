<?php

namespace App\Services\Director;

use App\Models\Director\Director;
use Illuminate\Support\Facades\Config;

class DirectorService {

    /**
     * Return 20 last added directors
     * 
     * @return Director
     */
    public function getDirectors()
    {
        $directors = Director::orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->paginate(20);
        return $directors;
    }

    /**
     * Delete director
     * 
     * @return void
     */
    public function deleteDirector(Director $director)
    {
        $director->update(['status' => Config::get('common.status.deleted')]);
    }

    /**
     * Return result of search Director
     * 
     * @return Director
     */
    public function searchDirector($value)
    {
        $directors = Director::orderBy('created_at', 'DESC')
                                ->where('status', Config::get('common.status.actived'))
                                ->whereRaw("concat(first_name, ' ', last_name) like '%".$value."%'")
                                ->paginate(20);
        return $directors;
    }

}