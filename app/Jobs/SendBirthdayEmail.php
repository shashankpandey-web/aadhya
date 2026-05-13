<?php
namespace App\Jobs;
use App\Models\Customers;
//use App\Mail\BirthdayWishMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Mail;

class SendBirthdayEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $Customer;

    public function __construct(Customers $Customer)
    {
        $this->Customer = $Customer;
    }

    public function handle()
    {
        try {       
            $data = array(); 
            $data['name']  = $this->Customer->full_name;
            //$data['email'] = $this->Customer->email;
            $data['email'] = 'dev.infosparkles@gmail.com';
            $data['phone_number']  = $this->Customer->phone_number;

            if ($data['email']!='') {
                $data['subject']       = 'Happy Birthday!';
                $data['template']      = 'email.birthday_wish';
                if ($data['template']) {
                    Mail::send($data['template'], $data, function ($message) use ($data) {
                        $message->to($data['email'], $data['name'])->subject($data['subject']);
                    });
                }
            }

            Log::info("Happy Birthday! {$this->Customer->full_name} for Customer #{$this->Customer->id}");
            /*Log::info("SendBirthdayEmail successful", [
                'Customer' => $this->Customer
            ]);*/
        } catch (Exception $e) {
            /*Log::info("SendBirthdayEmail error", [
                'Customer' => $this->Customer
            ]);*/
        }
    }
}
