<?php

namespace App\Services;

use App\Logs\TelegramLog;
use App\Models\API\TelegramUser;
use App\Models\API\User;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

class TelegramUserService {

    private $commands = [];
    private $telegramLog;

    /**
     * Bootstrap class
     * 
     * @return void
     */
    public function __construct()
    {
        // commands
        $this->commands = [    
            '/start' => 'Hello from platform.',
            '/help' => 'Help section.',
            '/link' => env('APP_FRONTEND_ENDPOINT'),
            '/profile' => 'You don\'t have profile in app yet.',
        ];

        $this->telegramLog = new TelegramLog();
    }

    /**
     * Return telegram updates
     * 
     * @return array
     */
    public function getUpdates() 
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, TRUE);
        return $request;
    }

    /**
     * Return entity
     * 
     * @return array
     */
    public function getEntity($message)
    {
        $msg = $this->getMessage($message);

        $username = $message['from']['id'];
        if (isset($message['from']['username'])){
            $username = $message['from']['username'];
        }

        return [
            'telegram_id' => (string) $message['from']['id'],
            'is_bot' => $message['from']['is_bot'],
            'first_name' => $message['from']['first_name'],
            'username' => $username,
            'language_code' => $message['from']['language_code'],
            'message' => $msg['msg'],
            'context' => $msg['context'],
        ];
    }

    /**
     * Return telegram message type
     * 
     * @return array
     */
    private function getMessage($message)
    {
        $result = [ 'msg' => '', 'context' => '' ];

        if (isset($message['text'])){ // text
            $result = [ 'msg' => $message['text'], 'context' => '' ];
        }

        return $result;
    }

    /**
     * Return telegram user by telegram id
     * 
     * @return void
     */
    public function createTelegramUser($entity)
    {
        ini_set('memory_limit', '1024M');
        $telegram_user = TelegramUser::where('telegram_id', $entity['telegram_id'])
                                        ->first();
        if ($telegram_user==null){
            TelegramUser::create($entity);
        }else{
            $telegram_user->update($entity);
        }
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
                    $msg_response = $this->getUserViaTelegram($entity);
                    break;
            }
            
        }

        return $msg_response;
    }

    /**
     * Return User via telegram
     * 
     * @return User
     */
    private function getUserViaTelegram($entity)
    {
        $response = $this->commands[$entity['message']];
        $user = User::where('telegram', $entity['username'])
                        ->where('status', Config::get('common.status.actived'))
                        ->first();
        if ($user!=null){
            $response = 'Your user ID: *' . $user['uuid'] . "*\n" 
                        .   'Your First Name: *' . $user['first_name'] . "*\n"
                        .   'Your Last Name: *' . $user['last_name'] . "*\n"
                        .   'Your Username: *' . $user['username'] . "*\n"
                        .   'Your Password: *' . $user['password'] . "*\n";

        }
        return $response;
    }
}