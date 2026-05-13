<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Customers;
use App\Models\Customernotificationlogs;
use Carbon\Carbon;

class SendNotificationLongPeriodOffline extends Command
{
    protected $signature = 'notification:send-long-period-offline';
    protected $description = 'Send notifications to users who have been offline for more than 30 minutes.';

    public function handle()
    {
        $thirtyMinutesAgo = Carbon::now()->subMinutes(30);
        Customers::where('is_online_last', '<', $thirtyMinutesAgo)
            ->whereNull('is_delete')->where(['status' => 'Active', 'type' => 'Customer'])
            ->chunk(1000, function ($customers) {
                foreach ($customers as $customer) {
                    $lastNotification = Customernotificationlogs::where('customer_id', $customer->id)->where('event_type', 'send_long_period_offline')->orderBy('created_at', 'desc')->first();
                    
                    if (!$lastNotification || $lastNotification->created_at->lt($thirtyMinutesAgo)){
                        $Customernotificationlogs = new Customernotificationlogs();
                        $Customernotificationlogs->customer_id = $customer->id;
                        $Customernotificationlogs->type = 'notification';
                        $Customernotificationlogs->event_type = 'send_long_period_offline';
                        $Customernotificationlogs->save();
                    }

                }
            });

        $this->info('Offline notifications processed successfully!');
    }
}
