<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveOfferSettings extends Migration
{
    public function up()
    {
        DB::table('settings')->whereIn('meta_title', [
            'birthday_discount_percentage',
            'comeback_offer_percentage',
        ])->delete();
    }

    public function down()
    {
        DB::table('settings')->insert([
            [
                'meta_title' => 'birthday_discount_percentage',
                'title'      => 'Birthday Discount Percentage',
                'content'    => '10',
                'type'       => 'number',
                'status'     => 'Active',
            ],
            [
                'meta_title' => 'comeback_offer_percentage',
                'title'      => 'Comeback Offer Percentage',
                'content'    => '10',
                'type'       => 'number',
                'status'     => 'Active',
            ],
        ]);
    }
}
