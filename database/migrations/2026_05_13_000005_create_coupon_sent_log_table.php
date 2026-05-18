<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponSentLogTable extends Migration
{
    public function up()
    {
        Schema::create('coupon_sent_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedInteger('customer_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_sent_log');
    }
}
