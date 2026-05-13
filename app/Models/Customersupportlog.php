<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Customersupportlog extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'customer_support_log';
}
