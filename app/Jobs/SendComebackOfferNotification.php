<?php
namespace App\Jobs;

use App\Models\Customers;
use App\Models\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mail;

class SendComebackOfferNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $Customer;
    protected $coupon_code;
    protected $discount_percentage;

    public function __construct(Customers $Customer, $coupon_code = null, $discount_percentage = 0)
    {
        $this->Customer            = $Customer;
        $this->coupon_code         = $coupon_code;
        $this->discount_percentage = $discount_percentage;
    }

    public function handle()
    {
        try {
            $title = $this->coupon_code && $this->discount_percentage > 0
                ? "We miss you! Use code {$this->coupon_code} to get {$this->discount_percentage}% off your next reading."
                : "We miss you! Come back and book your next reading.";

            // Save in-app notification
            $Notification              = new Notifications();
            $Notification->customer_id = $this->Customer->id;
            $Notification->title       = $title;
            $Notification->type        = 'comeback-offer';
            $Notification->save();

            // Send FCM push notification
            if ($this->Customer->fcm_token) {
                $request       = new \stdClass();
                $request->data = '';
                send_customer_notification(
                    'We Miss You! 💫',
                    $title,
                    $this->Customer->fcm_token,
                    $request
                );
            }

            // Send email
            if ($this->Customer->email) {
                $data                        = [];
                $data['name']                = $this->Customer->full_name;
                $data['email']               = $this->Customer->email;
                $data['coupon_code']         = $this->coupon_code;
                $data['discount_percentage'] = $this->discount_percentage;
                $data['subject']             = 'We Miss You – Here\'s a Special Comeback Offer!';
                $data['template']            = 'email.comeback_offer';

                Mail::send($data['template'], $data, function ($message) use ($data) {
                    $message->to($data['email'], $data['name'])->subject($data['subject']);
                });
            }

            Log::info("Comeback offer sent to {$this->Customer->full_name} (ID: {$this->Customer->id}), code: {$this->coupon_code}, discount: {$this->discount_percentage}%");
        } catch (\Exception $e) {
            Log::error("SendComebackOfferNotification failed for Customer #{$this->Customer->id}: " . $e->getMessage());
        }
    }
}
