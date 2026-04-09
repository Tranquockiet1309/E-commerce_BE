<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // ✅ Nên đặt tên bảng đúng với DB: 'orders'
    protected $table = 'order';

    // ✅ Cột được phép ghi
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'note',
        'status',
        'created_by',
        'updated_by'
    ];

    // ✅ Laravel tự tạo created_at & updated_at
    public $timestamps = true;

    // ✅ Quan hệ 1 - N với OrderDetail
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    // ✅ Quan hệ N - 1 với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
