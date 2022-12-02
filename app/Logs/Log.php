<?php

namespace App\Logs;

class Log {

    public function to_file($array, $prefix = '')
    {
        $f = fopen('log/'.$prefix.'_'.date('ymdHi').'.txt', 'w+');
        fwrite($f, print_r($array, TRUE));
        fclose($f);
    }

}