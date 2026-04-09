<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting'; // Nếu bảng là "setting" (số ít)
    public $timestamps = false;
    protected $fillable = [
        'site_name',
        'email',
        'phone',
        'hotline',
        'address',
        'status',
    ];
}

