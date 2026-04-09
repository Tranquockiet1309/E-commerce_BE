<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'orderdetail';

    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'qty',
        'amount',
        'discount',
        'attributes',
    ];

    public $timestamps = false; // nếu bảng không có created_at / updated_at
    protected $casts = [
        'attributes' => 'array', // tự động convert JSON ↔ array
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
