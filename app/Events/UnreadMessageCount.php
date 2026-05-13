<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class UnreadMessageCount implements ShouldBroadcast{

    use SerializesModels;

    public $receiverId;
    public $unreadCounts;
    public $senderId;
    public $recent_message;
    public $recent_message_timestamp;
    public $file_type;
    public $file_name;
    
    

    public function __construct($receiverId,$unreadCounts,$senderId,$recent_message,$recent_message_timestamp,$file_type,$file_name){
        $this->unreadCounts = $unreadCounts;
        $this->receiverId = $receiverId;
        $this->senderId = $senderId;
        $this->recent_message = $recent_message;
        $this->recent_message_timestamp = $recent_message_timestamp;
        $this->file_type = $file_type;
        $this->file_name = $file_name;
    }
   

    public function broadcastOn(){
        Log::info('UnreadMessageCount Event: Broadcasting unread counts for all users');
        return new Channel('user-unread-messages');
    }


    public function broadcastWith()
    {
        return [
            'receiverId' => $this->receiverId,
            'unreadCounts' => $this->unreadCounts,
            'senderId' => $this->senderId,
            'recent_message' => $this->recent_message,
            'date_time' => $this->recent_message_timestamp,
            'file_type' => $this->file_type,
            'file_name' => $this->file_name,
        ];
    }

    public function broadcastAs(){
        return 'UnreadMessageCount';
    }
}