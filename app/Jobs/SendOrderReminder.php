<?php
namespace App\Jobs;

use App\Models\Customers;
use App\Models\Notifications;
use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mail;

class SendOrderReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $interval;

    public function __construct(Orders $order, $interval)
    {
        $this->order    = $order;
        $this->interval = $interval;
    }

    public function handle()
    {
        $messages = [
            '15-min-remaining'  => 'You have 15 minutes remaining to submit the reading.',
            '1-hour-remaining'  => 'You have 1 hour remaining to submit the reading.',
            '6-hour-remaining'  => 'You have 6 hours remaining to submit the reading.',
            '12-hour-remaining' => 'You have 12 hours remaining to submit the reading.',
        ];

        $time_remaining_labels = [
            '15-min-remaining'  => '15 minutes',
            '1-hour-remaining'  => '1 hour',
            '6-hour-remaining'  => '6 hours',
            '12-hour-remaining' => '12 hours',
        ];

        $body = $messages[$this->interval] ?? 'Reminder: please submit your reading.';
        $title = 'Order Reminder #' . $this->order->id;

        $advisor = Customers::where('id', $this->order->advisore_id)->first();
        if (!$advisor) {
            Log::warning("SendOrderReminder: advisor not found for order #{$this->order->id}");
            return;
        }

        try {
            $Notification              = new Notifications();
            $Notification->customer_id = $advisor->id;
            $Notification->title       = $body;
            $Notification->type        = 'order-reminder';
            $Notification->order_id    = $this->order->id;
            $Notification->save();

            if ($advisor->fcm_token) {
                $request       = new \stdClass();
                $request->data = '';
                send_customer_notification($title, $body, $advisor->fcm_token, $request);
            }

            if ($advisor->email) {
                $emailData = [
                    'name'           => $advisor->full_name,
                    'email'          => $advisor->email,
                    'order_id'       => $this->order->id,
                    'time_remaining' => $time_remaining_labels[$this->interval] ?? 'some time',
                    'subject'        => "Reminder: {$time_remaining_labels[$this->interval] ?? 'Time'} left to submit reading for Order #{$this->order->id}",
                ];
                Mail::send('email.order_reminder', $emailData, function ($message) use ($emailData) {
                    $message->to($emailData['email'], $emailData['name'])->subject($emailData['subject']);
                });
            }

            Log::info("SendOrderReminder [{$this->interval}] sent to advisor #{$advisor->id} for order #{$this->order->id}");
        } catch (\Exception $e) {
            Log::error("SendOrderReminder failed for order #{$this->order->id}: " . $e->getMessage());
        }
    }
}
