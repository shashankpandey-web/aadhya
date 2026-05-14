<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfferFlagsToCoupon extends Migration
{
    public function up()
    {
        Schema::table('coupon', function (Blueprint $table) {
            $table->tinyInteger('is_comeback_offer')->default(0)->after('status');
            $table->tinyInteger('is_birthday_offer')->default(0)->after('is_comeback_offer');
        });
    }

    public function down()
    {
        Schema::table('coupon', function (Blueprint $table) {
            $table->dropColumn(['is_comeback_offer', 'is_birthday_offer']);
        });
    }
}
