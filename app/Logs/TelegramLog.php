<?php

namespace App\Logs;

class TelegramLog {

    public function to_file($array)
    {
        $f = fopen('telegram_log/t'.date('ymdHi').'.txt', 'w');
        fwrite($f, print_r($array, TRUE));
        fclose($f);
    }

}