<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBirthdayDiscountSetting extends Migration
{
    public function up()
    {
        $exists = DB::table('settings')
            ->where('meta_title', 'birthday_discount_percentage')
            ->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'type'          => 'number',
                'meta_title'    => 'birthday_discount_percentage',
                'title'         => 'Birthday Readings Discount (%)',
                'content'       => '0',
                'status'        => 'active',
                'column_status' => 'active',
                'sort'          => 3,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('settings')->where('meta_title', 'birthday_discount_percentage')->delete();
    }
}
