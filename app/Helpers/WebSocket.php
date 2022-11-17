<?php

namespace App\Helpers;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WebSocket implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    private $section;
    private $user;

    public function __construct($data)
    {
        $this->section = $data['section'];
        $this->data = $data['data'];
        $this->user = $data['user'];
    }

    public function broadcastOn()
    {
        return [$this->section.$this->user['uuid']];
    }

    public function broadcastAs()
    {
        return $this->section.'-push';
    }
}