<?php

namespace App\Services;

use App\Models\API\TelegramUser;
use App\Models\API\User;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Config;
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
            '/profile' => 'You don\'t have profile in app yet.',
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
            // special commands
            switch ($entity['message']){
                case '/start':
                case '/help':
                case '/link':
                    $msg_response = $this->commands[$entity['message']];
                    break;
                case '/profile':
                    $user = $this->getUserViaTelegram($entity['username']);
                    if ($user!=null){
                        $msg_response = 'Your user ID: *' . $user['uuid'] . "*\n" 
                                    .   'Your First Name: *' . $user['first_name'] . "*\n"
                                    .   'Your Last Name: *' . $user['last_name'] . "*\n"
                                    .   'Your Username: *' . $user['username'] . "*\n"
                                    .   'Your Password: *' . $user['password'] . "*\n";

                    }
                    break;
            }
            
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

    /**
     * Return User via telegram
     * 
     * @return User
     */
    private function getUserViaTelegram($telegram)
    {
        $user = User::where('telegram', $telegram)
                        ->where('status', Config::get('common.status.actived'))
                        ->first();
        return $user;
    }
}