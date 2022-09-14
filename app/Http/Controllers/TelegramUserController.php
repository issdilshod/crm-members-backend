<?php

namespace App\Http\Controllers;

use App\Models\API\TelegramUser;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Notification;

class TelegramUserController extends Controller
{

    public function index(){
        // commands
        $commands = [
            '/start' => 'Hello from platform.'
        ];

        $request = file_get_contents('php://input');
        $request = json_decode( $request, TRUE );

        // getting message
        $request = $request['message'];

        $entity = [
            'telegram_id' => $request['from']['id'],
            'is_bot' => $request['from']['is_bot'],
            'first_name' => $request['from']['first_name'],
            'username' => $request['from']['username'],
            'language_code' => $request['from']['language_code'],
            'message' => $request['text']
        ];

        $telegram_user = TelegramUser::where('telegram_id', $entity['telegram_id'])->first();
        if ($telegram_user==null){
            TelegramUser::create($entity);
        }

        // send message response
        $msg_response = 'If there are some news on platform, we will send message to you!';
        if (isset($commands[$entity['message']])){
            $msg_response = $commands[$entity['message']];
        }

        $f = fopen('uploads/telegram.txt', 'w');
        fwrite($f, $msg_response);
        fclose($f);

        Notification::route('telegram', $entity['telegram_id'])
                      ->notify(new TelegramNotification(['msg' => $msg_response]));

    }

}
