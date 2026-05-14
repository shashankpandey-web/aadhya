<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customers;
use App\Models\Orders;
use App\Models\Coupon;
use App\Models\CouponSentLog;
use App\Jobs\SendComebackOfferNotification;
use Carbon\Carbon;

class SendComebackOfferNotifications extends Command
{
    protected $signature = 'notification:send-comeback-offer';
    protected $description = 'Send comeback offer notifications to customers who have not booked a reading in the last 7 days.';

    public function handle()
    {
        $comebackCoupon = Coupon::where('is_comeback_offer', 1)
            ->where('status', 'Active')
            ->whereNull('is_delete')
            ->first();

        if (!$comebackCoupon) {
            $this->warn('No active comeback offer coupon found. Mark a coupon with "Comeback Offer" enabled in the admin panel.');
            return;
        }

        $coupon_code         = $comebackCoupon->code;
        $discount_percentage = (int) $comebackCoupon->value;

        $sevenDaysAgo = Carbon::now()->subDays(7);

        // Customer IDs who placed an order within the last 7 days — exclude them
        $recentOrderCustomerIds = Orders::where('created_at', '>=', $sevenDaysAgo->toDateTimeString())
            ->pluck('customer_id')
            ->unique()
            ->toArray();

        // Customer IDs already sent this coupon within the last 7 days — exclude them
        $alreadySentCustomerIds = CouponSentLog::where('coupon_id', $comebackCoupon->id)
            ->where('created_at', '>=', $sevenDaysAgo->toDateTimeString())
            ->pluck('customer_id')
            ->unique()
            ->toArray();

        $excludedIds = array_unique(array_merge($recentOrderCustomerIds, $alreadySentCustomerIds));

        Customers::whereNull('is_delete')
            ->where(['status' => 'Active', 'type' => 'Customer'])
            ->whereNotIn('id', $excludedIds)
            ->chunk(1000, function ($customers) use ($comebackCoupon, $coupon_code, $discount_percentage) {
                foreach ($customers as $customer) {
                    SendComebackOfferNotification::dispatch($customer, $coupon_code, $discount_percentage);

                    $sentLog              = new CouponSentLog();
                    $sentLog->coupon_id   = $comebackCoupon->id;
                    $sentLog->customer_id = $customer->id;
                    $sentLog->save();
                }
            });

        $this->info('Comeback offer notifications dispatched!');
    }
}
