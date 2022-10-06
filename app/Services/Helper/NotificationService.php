<?php

namespace App\Services\Helper;

use App\Helpers\TelegramHelper;
use App\Helpers\WebSocket;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService {

    /**
     * Send notification via websocket
     * 
     * @param   Array user, msg, link
     * @return  void
     */
    public function push($entity)
    {
        event(new WebSocket([
                    'user' => $entity['user'], 
                    'data' => [
                        'msg' => $entity['msg'],
                        'link' => $entity['link'],
                        'push' => true
                    ]
                ])
        );
    }

    /**
     * Send notification to telegram
     * 
     * @param   Array telegram, msg
     * @return  void
     */
    public function telegram($entity)
    {
        $chat_id = TelegramHelper::getTelegramChatId($entity['telegram']);
        if ($chat_id!=null){
            Notification::route('telegram', $chat_id)
                    ->notify(new TelegramNotification(['msg' => $entity['msg']]));
        }
    }

}