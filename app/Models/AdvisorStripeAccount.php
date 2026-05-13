<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class AdvisorStripeAccount extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'advisor_stripe_account';
}
