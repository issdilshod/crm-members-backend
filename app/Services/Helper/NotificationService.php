<?php

namespace App\Services\Helper;

use App\Helpers\TelegramHelper;
use App\Helpers\WebSocket;
use App\Models\Account\User;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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

    /**
     * Send telegram notification to headquarters
     * 
     * @param   Array msg
     * @return  void
     */
    public function telegram_to_headqurters($msg)
    {
        // get headquarters uuid
        $users = DB::table('users')
                        ->join('roles', function ($join){
                            $join->on('users.role_uuid', '=', 'roles.uuid')
                                    ->where('roles.alias', Config::get('common.role.headquarters'));
                        })
                        ->select('users.telegram')
                        ->get();
        // each users (headquarters)
        foreach($users AS $key => $value):
            $chat_id = TelegramHelper::getTelegramChatId($value->telegram);
            if ($chat_id!=null){
                Notification::route('telegram', $chat_id)
                        ->notify(new TelegramNotification(['msg' => $msg]));
            }
        endforeach;
    }

}