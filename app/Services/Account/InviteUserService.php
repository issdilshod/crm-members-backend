<?php

namespace App\Services\Account;

use App\Helpers\TelegramHelper;
use App\Helpers\UserSystemInfoHelper;
use App\Mail\EmailInvite;
use App\Models\Account\Activity;
use App\Models\Account\InviteUser;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteUserService {

    public function via_email($entity)
    {
        $entity['entry_token'] = Str::random(32);
        $entity['via'] = Config::get('common.invite.email');

        // send mail
        $link = env('APP_FRONTEND_ENDPOINT') . '/register/'. $entity['entry_token'];
        Mail::to($entity['unique_identify'])
                ->send(new EmailInvite($link));

        $invite_user = InviteUser::create($entity);

        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $invite_user['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.user.invite_via_email'),
            'changes' => json_encode($invite_user->toArray()),
            'action_code' => Config::get('common.activity.codes.user_invite_via_email'),
            'status' => Config::get('common.status.actived')
        ]);

        return response()->json([
            'data' => 'Success',
        ], 200);
    }

    public function via_telegram($entity)
    {
        $entity['entry_token'] = Str::random(32);
        $entity['via'] = Config::get('common.invite.telegram');

        $link = env('APP_FRONTEND_ENDPOINT') . '/register/'. $entity['entry_token'];
        $chat_id = TelegramHelper::getTelegramChatId($entity['unique_identify']);
        if ($chat_id==null){ // if user not start bot
            return response()->json([
                'data' => 'Not found chat with this user.',
            ], 404);
        }

        $invite_user = InviteUser::create($entity);

        Notification::route('telegram', $chat_id)
                        ->notify(new TelegramNotification(['msg' => 'Hello from platform! This is your link for [register]('.$link.')']));

        Activity::create([
            'user_uuid' => $entity['user_uuid'],
            'entity_uuid' => $invite_user['uuid'],
            'device' => UserSystemInfoHelper::device_full(),
            'ip' => UserSystemInfoHelper::ip(),
            'description' => Config::get('common.activity.user.invite_via_telegram'),
            'changes' => json_encode($entity),
            'action_code' => Config::get('common.activity.codes.user_invite_via_telegram'),
            'status' => Config::get('common.status.actived')
        ]);

        return response()->json([
            'data' => 'Success',
        ], 200);
    }

    public function check_token($entity)
    {
        $invite_user = InviteUser::select('unique_identify', 'via', 'entry_token')
                        ->where('status', Config::get('common.status.actived'))
                        ->where('entry_token', $entity['entry_token'])
                        ->first();
        
        if ($invite_user!=null){
            return response()->json([
                'data' => $invite_user->toArray(),
            ], 200);
        }

        return response()->json([
            'data' => 'Invalid token!',
        ], 404);
    }

}