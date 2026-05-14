<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customers;
use App\Models\Coupon;
use App\Jobs\SendBirthdayEmail;
use Carbon\Carbon;

class SendBirthdayEmails extends Command
{
    protected $signature = 'email:send-birthday';
    protected $description = 'Send birthday emails to Customers';

    public function handle()
    {
        $yesterday = Carbon::yesterday()->format('m-d');

        Customers::whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", [$yesterday])
            ->whereNull('is_delete')
            ->where(['status' => 'Active', 'type' => 'Customer'])
            ->where('birthday_email_sent', 1)
            ->update(['birthday_email_sent' => 0]);

        $today = Carbon::today()->format('m-d');

        $birthdayCoupon = Coupon::where('is_birthday_offer', 1)
            ->where('status', 'Active')
            ->whereNull('is_delete')
            ->first();

        $discount_percentage = $birthdayCoupon ? (int) $birthdayCoupon->value : 0;

        Customers::whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", [$today])
            ->where('birthday_email_sent', 0)
            ->whereNull('is_delete')
            ->where(['status' => 'Active', 'type' => 'Customer'])
            ->chunk(1000, function ($Customers) use ($discount_percentage) {
                foreach ($Customers as $Customer) {
                    SendBirthdayEmail::dispatch($Customer, $discount_percentage);
                    $Customer->birthday_email_sent = 1;
                    $Customer->save();
                }
            });

        $this->info('Birthday emails dispatched!');
    }
}
