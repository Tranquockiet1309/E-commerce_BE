<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $table = 'product_attribute';

    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
        'extra_price',
    ];

    // Thuộc về 1 sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Thuộc về 1 attribute (Màu sắc, Kích thước...)
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
