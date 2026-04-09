<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $table = 'attribute';
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    // Một Attribute có thể thuộc về nhiều Product (qua bảng product_attributes)
    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
}
