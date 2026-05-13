<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class AdvisorBanks extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'advisor_banks';
}
