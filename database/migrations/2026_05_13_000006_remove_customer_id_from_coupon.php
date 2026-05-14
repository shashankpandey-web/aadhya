<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RemoveCustomerIdFromCoupon extends Migration
{
    public function up()
    {
        // Remove per-customer comeback coupons created by the old approach
        DB::table('coupon')->where('title', 'Comeback Offer')->delete();

        Schema::table('coupon', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
    }

    public function down()
    {
        Schema::table('coupon', function (Blueprint $table) {
            $table->unsignedInteger('customer_id')->nullable()->after('type');
        });
    }
}
