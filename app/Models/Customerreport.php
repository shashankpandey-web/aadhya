<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Customerreport extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'customer_report';
}
