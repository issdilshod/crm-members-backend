<?php

namespace App\Helpers;

use App\Models\Account\TelegramUser;

class TelegramHelper{

    public static function getTelegramChatId($nickname){
        $telegram_user = TelegramUser::where('username', $nickname)->first();
        $chat_id = null;
        if ($telegram_user!=null){
            $telegram_user = $telegram_user->toArray();
            $chat_id = $telegram_user['telegram_id'];
        }
        return $chat_id;
    }
}