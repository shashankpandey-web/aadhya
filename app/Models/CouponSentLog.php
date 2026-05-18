<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponSentLog extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $table   = 'coupon_sent_log';
    protected $guarded = [];
}
