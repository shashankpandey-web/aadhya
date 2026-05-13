<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Customernotificationlogs extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'customer_notification_logs';
}
