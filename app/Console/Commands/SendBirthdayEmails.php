<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Customers;
use App\Jobs\SendBirthdayEmail;
use Carbon\Carbon;

class SendBirthdayEmails extends Command
{
    protected $signature = 'email:send-birthday';
    protected $description = 'Send birthday emails to Customers';

    public function handle()
    {
        $yesterday = Carbon::yesterday()->format('m-d');

        Customers::whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", [$yesterday])->whereNull('is_delete')->where(['status' => 'Active','type' => 'Customer'])->where('birthday_email_sent', 1)->update(['birthday_email_sent' => 0]);

        $today = Carbon::today()->format('m-d'); // format month-day

        // Chunking to handle large datasets efficiently
        Customers::whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", [$today])
            ->where('birthday_email_sent', 0)->whereNull('is_delete')->where(['status' => 'Active','type' => 'Customer'])
            ->chunk(1000, function ($Customers) {
                foreach ($Customers as $Customer) {
                    SendBirthdayEmail::dispatch($Customer);
                    $Customer->birthday_email_sent = 1; 
                    $Customer->save();
                }
            });

        $this->info('Birthday emails dispatched!');
    }
}
