<?php

namespace App\Services\Helper;

use App\Helpers\TelegramHelper;
use App\Helpers\WebSocket;
use App\Logs\TelegramLog;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NotificationService {

    public function push($section, $user, $entity)
    {
        event(new WebSocket([
                'section' => $section,
                'user' => $user, 
                'data' => [
                    'msg' => $entity['msg'],
                    'link' => $entity['link'],
                    'data' => $entity['data']
                ]
            ])
        );
    }

    public function push_to_headquarters($section, $entity)
    {
        // get headquarters uuid
        $users = DB::table('users')
                        ->join('roles', function ($join){
                            $join->on('users.role_uuid', '=', 'roles.uuid')
                                    ->where('roles.alias', Config::get('common.role.headquarters'));
                        })
                        ->get(['users.*']);
                        $log  = new TelegramLog();
                        $log->to_file($entity);
        // each users (headquarters)
        foreach($users AS $key => $value):
            $this->push($section, json_decode(json_encode($value), true), $entity);
        endforeach;
    }

    public function telegram($entity)
    {
        $chat_id = TelegramHelper::getTelegramChatId($entity['telegram']);
        if ($chat_id!=null){
            Notification::route('telegram', $chat_id)
                    ->notify(new TelegramNotification(['msg' => $entity['msg']]));
        }
    }

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