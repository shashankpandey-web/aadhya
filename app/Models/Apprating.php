<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Apprating extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'app_rating';
}
