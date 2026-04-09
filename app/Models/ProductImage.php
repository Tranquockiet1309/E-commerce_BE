<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $table = 'product_image';
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'image',
        'alt',
        'title',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
