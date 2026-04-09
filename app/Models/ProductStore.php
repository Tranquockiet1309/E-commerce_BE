<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStore extends Model
{
    protected $table = 'product_store';
    protected $fillable = ['product_id', 'price_root', 'qty', 'status', 'created_by', 'updated_by'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
