<?php
namespace App\Jobs;
use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use Illuminate\Support\Facades\Log;

class SendOrderReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $interval;

    public function __construct(Orders $order, $interval)
    {
        $this->order = $order;
        $this->interval = $interval;
    }

    public function handle()
    {
        Log::info("Sending {$this->interval} reminder for order #{$this->order->id}");

        $data = array(); 
        $data['name']  = "Vijay ".$this->order->id." = ".$this->interval;
        $data['email'] = 'dev.infosparkles@gmail.com';

        if ($data['email']!='') {
            $data['subject']       = 'Happy SendOrderReminder! '.$this->order->id." = ".$this->interval;
            $data['template']      = 'email.birthday_wish';
            if ($data['template']) {
                Mail::send($data['template'], $data, function ($message) use ($data) {
                    $message->to($data['email'], $data['name'])->subject($data['subject']);
                });
            }
        }
    }
}
