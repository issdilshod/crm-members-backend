<?php

namespace App\Helpers;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WebSocket implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    private $user;

    public function __construct($data)
    {
        $this->data = $data['data'];
        $this->user = $data['user'];
    }

    public function broadcastOn()
    {
        return ['notification'.$this->user['uuid']];
    }

    public function broadcastAs()
    {
        return 'notification-push';
    }
}