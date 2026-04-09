<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'thumbnail',
        'content',
        'description',
        'price_buy',
        'status',
    ];
    //Quan hệ với ProductStore (kho hàng)
    public function stores()
    {
        return $this->hasMany(ProductStore::class, 'product_id', 'id');
    }
    // Quan hệ với bảng ProductSale
    public function sales()
    {
        // Một sản phẩm có thể có nhiều đợt khuyến mãi
        return $this->hasMany(ProductSale::class, 'product_id', 'id');
    }

    // Lấy khuyến mãi hiện tại (nếu có)
    public function currentSale()
    {
        return $this->hasOne(ProductSale::class, 'product_id', 'id')
            ->where('status', 1)
            ->whereDate('date_begin', '<=', now())
            ->whereDate('date_end', '>=', now());
    }
    // Ảnh nhiều cho sản phẩm
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
        // ProductImage là model của bảng lưu ảnh sản phẩm
    }

    // Thuộc tính nhiều cho sản phẩm
    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id');
        // ProductAttribute là model của bảng lưu thuộc tính sản phẩm
    }

}
