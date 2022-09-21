<?php

namespace App\Services;

use App\Models\API\TelegramUser;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Notification;

class TelegramUserService {

    private $commands = [];

    /**
     * Bootstrap class
     * 
     * @return void
     */
    public function __construct()
    {
        $this->commands = [    
            '/start' => 'Hello from platform.',
            '/help' => 'Help section.',
            '/link' => env('APP_FRONTEND_ENDPOINT'),
        ];
    }

    /**
     * Return telegram updates
     * 
     * @return array
     */
    public function getUpdates() 
    {
        $request = file_get_contents('php://input');
        $request = json_decode( $request, TRUE );
        return $request;
    }

    /**
     * Return entity
     * 
     * @return array
     */
    public function getEntity($message)
    {
        return [
            'telegram_id' => $message['from']['id'],
            'is_bot' => $message['from']['is_bot'],
            'first_name' => $message['from']['first_name'],
            'username' => $message['from']['username'],
            'language_code' => $message['from']['language_code'],
            'message' => $message['text']
        ];
    }

    /**
     * Return telegram user by telegram id
     * 
     * @return void
     */
    public function createTelegramUser($entity)
    {
        $telegram_user = TelegramUser::where('telegram_id', $entity['telegram_id'])
                                        ->first();
        if ($telegram_user==null){
            TelegramUser::create($entity);
        }
    }

    /**
     * Return message of respond
     * 
     * @return string
     */
    public function getResponse($entity)
    {
        $msg_response = 'If there are some news on platform, we will send message to you!';

        if (isset($this->commands[$entity['message']])){
            $msg_response = $this->commands[$entity['message']];
        }

        return $msg_response;
    }

    /**
     * Send response to user from bot
     * 
     * @return void
     */
    public function sendResponse($entity, $msg)
    {
        Notification::route('telegram', $entity['telegram_id'])
                      ->notify(new TelegramNotification(['msg' => $msg]));
    }
}