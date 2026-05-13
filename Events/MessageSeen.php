<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSeen implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $senderId;
    public $receiverId;

    public function __construct($receiverId, $senderId)
    {
        $this->receiverId = (int)$receiverId;
        $this->senderId = $senderId;
    }

    public function broadcastOn(){
        return new PrivateChannel("seen.{$this->receiverId}.{$this->senderId}");
    }

    public function broadcastAs()
    {
        return 'MessageSeen';
    }
}
