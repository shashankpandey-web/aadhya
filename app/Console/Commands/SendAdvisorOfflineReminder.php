<?php
namespace App\Console\Commands;

use App\Models\Customers;
use App\Models\Customernotificationlogs;
use App\Models\Notifications;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAdvisorOfflineReminder extends Command
{
    protected $signature   = 'notification:send-advisor-offline-reminder';
    protected $description = 'Remind advisors who have been offline for 2+ hours to come back online.';

    public function handle()
    {
        Log::info('SendAdvisorOfflineReminder: Command started.', ['time' => now()->toDateTimeString()]);

        $threshold = Carbon::now()->subHours(2);

        $title = 'Clients are waiting for you!';
        $body  = 'You\'ve been offline for a while. Log back in to Aadya and start accepting bookings.';

        $totalProcessed = 0;
        $totalSkipped   = 0;
        $totalSent      = 0;
        $totalFailed    = 0;

        Log::info('SendAdvisorOfflineReminder: Querying offline advisors.', [
            'threshold' => $threshold->toDateTimeString(),
        ]);

        Customers::where('type', 'Advisor')
            ->where('status', 'Active')
            ->whereNull('is_delete')
            ->where('is_online_last', '<', $threshold)
            ->chunk(1000, function ($advisors) use ($threshold, $title, $body, &$totalProcessed, &$totalSkipped, &$totalSent, &$totalFailed) {

                Log::info('SendAdvisorOfflineReminder: Processing chunk.', ['chunk_size' => $advisors->count()]);

                foreach ($advisors as $advisor) {
                    $totalProcessed++;

                    Log::info("SendAdvisorOfflineReminder: Checking advisor #{$advisor->id} ({$advisor->full_name}).", [
                        'advisor_id'     => $advisor->id,
                        'is_online_last' => $advisor->is_online_last,
                    ]);

                    // Check pending orders
                    $hasPendingOrders = Orders::where('advisore_id', $advisor->id)
                        ->whereIn('order_availability_id', [
                            config('avaiblityconfig.1_day_delivery'),
                            config('avaiblityconfig.1_hour_delivery'),
                        ])
                        ->where('is_todo', 0)
                        ->exists();

                    Log::info("SendAdvisorOfflineReminder: Pending orders check for advisor #{$advisor->id}.", [
                        'advisor_id'        => $advisor->id,
                        'has_pending_orders' => $hasPendingOrders,
                    ]);

                    if (!$hasPendingOrders) {
                        Log::info("SendAdvisorOfflineReminder: Skipping advisor #{$advisor->id} — no pending orders.", [
                            'advisor_id' => $advisor->id,
                        ]);
                        $totalSkipped++;
                        continue;
                    }

                    // Check last notification log
                    $lastLog = Customernotificationlogs::where('customer_id', $advisor->id)
                        ->where('event_type', 'advisor_offline_reminder')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    Log::info("SendAdvisorOfflineReminder: Last notification log check for advisor #{$advisor->id}.", [
                        'advisor_id'       => $advisor->id,
                        'last_log_id'      => $lastLog?->id,
                        'last_log_time'    => $lastLog?->created_at?->toDateTimeString() ?? 'none',
                    ]);

                    if ($lastLog && $lastLog->created_at->gt($threshold)) {
                        Log::info("SendAdvisorOfflineReminder: Skipping advisor #{$advisor->id} — already notified within threshold.", [
                            'advisor_id'    => $advisor->id,
                            'last_log_time' => $lastLog->created_at->toDateTimeString(),
                            'threshold'     => $threshold->toDateTimeString(),
                        ]);
                        $totalSkipped++;
                        continue;
                    }

                    try {
                        // Save notification log
                        $log              = new Customernotificationlogs();
                        $log->customer_id = $advisor->id;
                        $log->type        = 'notification';
                        $log->event_type  = 'advisor_offline_reminder';
                        $log->save();

                        Log::info("SendAdvisorOfflineReminder: Notification log saved for advisor #{$advisor->id}.", [
                            'advisor_id' => $advisor->id,
                            'log_id'     => $log->id,
                        ]);

                        // Save notification record
                        $Notification              = new Notifications();
                        $Notification->customer_id = $advisor->id;
                        $Notification->title       = $body;
                        $Notification->type        = 'advisor-offline-reminder';
                        $Notification->save();

                        Log::info("SendAdvisorOfflineReminder: Notification record saved for advisor #{$advisor->id}.", [
                            'advisor_id'      => $advisor->id,
                            'notification_id' => $Notification->id,
                        ]);

                        // Send FCM push notification
                        if ($advisor->fcm_token) {
                            Log::info("SendAdvisorOfflineReminder: Sending FCM push notification to advisor #{$advisor->id}.", [
                                'advisor_id' => $advisor->id,
                                'fcm_token'  => substr($advisor->fcm_token, 0, 10) . '...', // Partial token for security
                            ]);

                            $request       = new \stdClass();
                            $request->data = '';
                            send_customer_notification($title, $body, $advisor->fcm_token, $request);

                            Log::info("SendAdvisorOfflineReminder: FCM push notification sent to advisor #{$advisor->id}.", [
                                'advisor_id' => $advisor->id,
                            ]);
                        } else {
                            Log::warning("SendAdvisorOfflineReminder: Advisor #{$advisor->id} has no FCM token — push skipped.", [
                                'advisor_id' => $advisor->id,
                            ]);
                        }

                        Log::info("Advisor offline reminder sent to advisor #{$advisor->id} ({$advisor->full_name})");
                        $totalSent++;

                    } catch (\Exception $e) {
                        $totalFailed++;
                        Log::error("SendAdvisorOfflineReminder: Failed for advisor #{$advisor->id}.", [
                            'advisor_id' => $advisor->id,
                            'error'      => $e->getMessage(),
                            'trace'      => $e->getTraceAsString(),
                        ]);
                    }
                }

                Log::info('SendAdvisorOfflineReminder: Chunk processing complete.', [
                    'chunk_size' => $advisors->count(),
                ]);
            });

        Log::info('SendAdvisorOfflineReminder: Command finished.', [
            'total_processed' => $totalProcessed,
            'total_sent'      => $totalSent,
            'total_skipped'   => $totalSkipped,
            'total_failed'    => $totalFailed,
        ]);

        $this->info("Advisor offline reminders processed successfully. Sent: {$totalSent}, Skipped: {$totalSkipped}, Failed: {$totalFailed}");
    }
}