<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateChat implements ShouldBroadcast {

    use Dispatchable, SerializesModels;

    public $message;
    public $senderId;
    public $receiverId;
    public $filePreview;

    public function __construct($message, $senderId, $receiverId, $filePreview){
        $this->message     = $message;
        $this->senderId    = $senderId;
        $this->receiverId  = $receiverId;
        $this->filePreview  = $filePreview;
        \Log::info("Broadcasting message from {$senderId} to {$receiverId}: {$message}");
    }
   
    public function broadcastOn(){
        return new PrivateChannel("chat.{$this->receiverId}.{$this->senderId}");
    }

    public function broadcastAs(){
        return 'PrivateMessageSent';
    }

}
