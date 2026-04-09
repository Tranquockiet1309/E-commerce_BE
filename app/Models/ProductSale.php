<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSale extends Model
{
    protected $table = 'product_sale';
    // Cho phép fill dữ liệu qua create/update
    protected $fillable = [
        'name',
        'product_id',
        'price_sale',
        'date_begin',
        'date_end',
        'status',
        'created_by',
        'updated_by'
    ];

    // Quan hệ: Mỗi khuyến mãi thuộc về 1 sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
