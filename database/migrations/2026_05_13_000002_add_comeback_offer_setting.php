<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddComebackOfferSetting extends Migration
{
    public function up()
    {
        $exists = DB::table('settings')
            ->where('meta_title', 'comeback_offer_percentage')
            ->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'type'          => 'number',
                'meta_title'    => 'comeback_offer_percentage',
                'title'         => 'Comeback Offer Discount (%)',
                'content'       => '0',
                'status'        => 'active',
                'column_status' => 'active',
                'sort'          => 4,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('settings')->where('meta_title', 'comeback_offer_percentage')->delete();
    }
}
