<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeCustomerIdToCoupon extends Migration
{
    public function up()
    {
        Schema::table('coupon', function (Blueprint $table) {
            $table->enum('type', ['flat', 'percentage'])->default('flat')->after('value');
            $table->unsignedInteger('customer_id')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('coupon', function (Blueprint $table) {
            $table->dropColumn(['type', 'customer_id']);
        });
    }
}
