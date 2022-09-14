<?php

namespace App\Helpers;

use NotificationChannels\Telegram\TelegramUpdates;

class TelegramHelper{

    public static function getTelegramChatId($nickname){
        $updates = TelegramUpdates::create()->get();
        $chat_id = null;
        foreach ($updates['result'] AS $key => $value):
            if ($value['message']['chat']['username'] == $nickname){
                $chat_id = $value['message']['chat']['id'];
                break;
            }
        endforeach;
        return $chat_id;
    }

}