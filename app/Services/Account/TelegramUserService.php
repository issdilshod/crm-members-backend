<?php

namespace App\Services\Account;

use App\Logs\TelegramLog;
use App\Models\Account\TelegramUser;
use App\Models\Account\User;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

class TelegramUserService {

    private $commands;
    private $updates;
    private $entity;
    private $response;
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

    public function init()
    {
        $this->updates = file_get_contents('php://input');
        $this->updates = json_decode($this->updates, TRUE);
        $this->set_entity();
        $this->create_user();$this->telegramLog->to_file('correct');
        $this->set_response();
        $this->send_response();
    }

    private function set_entity()
    {
        $this->entity = $this->updates['messages'];
        $message = $this->get_message($this->entity);

        $username = $message['from']['id'];
        if (isset($message['from']['username'])){
            $username = $message['from']['username'];
        }

        $this->entity = [
            'telegram_id' => (string) $this->entity['from']['id'],
            'is_bot' => $this->entity['from']['is_bot'],
            'first_name' => $this->entity['from']['first_name'],
            'username' => $username,
            'language_code' => $this->entity['from']['language_code'],
            'message' => $message['msg'],
            'context' => $message['context'],
        ];
    }

    private function get_message($message)
    {
        $result = [ 'msg' => '', 'context' => '' ];
        if (isset($message['text'])){ // text
            $result = [ 'msg' => $message['text'], 'context' => '' ];
        }
        return $result;
    }

    private function create_user()
    {
        $telegram_user = TelegramUser::where('telegram_id', $this->entity['telegram_id'])
                                        ->first();
        if ($telegram_user==null){
            TelegramUser::create($this->entity);
        }else{
            $telegram_user->update($this->entity);
        }
    }

    private function set_response()
    {
        $this->msg_response = 'If there are some news on platform, we will send message to you!';

        if (isset($this->commands[$this->entity['message']])){
            // special commands
            switch ($this->entity['message']){
                case '/start':
                case '/help':
                case '/link':
                    $this->msg_response = $this->commands[$this->entity['message']];
                    break;
                case '/profile':
                    $this->msg_response = $this->getUserViaTelegram($this->entity);
                    break;
            }
        }
    }

    private function send_response()
    {
        Notification::route('telegram', $this->entity['telegram_id'])
                      ->notify(new TelegramNotification(['msg' => $this->response]));
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