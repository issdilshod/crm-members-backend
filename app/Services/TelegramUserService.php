<?php

namespace App\Services;

use App\Models\API\TelegramUser;
use App\Models\API\User;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

class TelegramUserService {

    private $commands = [];
    private $types = [];

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
            '/voice' => 'Voice messages not supported yet.',
            '/document' => 'Document messages not supported yet.',
            '/audio' => 'Audio not supported yet.',
        ];

        // types
        $this->types = [
            'voice' => '/voice',
            'document' => '/document',
            'video_note' => '/video_note',
            'audio' => '/video_note',
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
        return [
            'telegram_id' => $message['from']['id'],
            'is_bot' => $message['from']['is_bot'],
            'first_name' => $message['from']['first_name'],
            'username' => $message['from']['username'],
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
        $result = [ 'msg' => '/unknown' ];

        if (isset($message['text'])){ // text
            $result = [ 'msg' => $message['text'], 'context' => '' ];
        }else if (isset($message['voice'])){ // voice
            $result = [ 'msg' => $this->types['voice'], 'context' => $message['voice'] ];
        }else if (isset($message['document'])){ // document
            $result = [ 'msg' => $this->types['document'], 'context' => '' ];
        }else if (isset($message['video_note'])){ // video note
            $result = [ 'msg' => $this->types['video_note'], 'context' => '' ];
        }else if (isset($message['audio'])){ // audio
            $result = [ 'msg' => $this->types['audio'], 'context' => '' ];
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
        $telegram_user = TelegramUser::where('telegram_id', $entity['telegram_id'])
                                        ->first();
        if ($telegram_user==null){
            TelegramUser::create($entity);
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
                case '/voice':
                    $msg_response = $this->commands[$entity['message']];
                    break;
                case '/document':
                    $msg_response = $this->commands[$entity['message']];
                    break;
                case '/video_note':
                    $msg_response = $this->commands[$entity['message']];
                    break;
                case '/audio':
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