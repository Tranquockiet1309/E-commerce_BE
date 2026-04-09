<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory;

    protected $table = 'topic';

    /**
     * Các cột được phép gán hàng loạt (mass assignment)
     */
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Laravel tự động quản lý created_at và updated_at
     */
    public $timestamps = true;

    /**
     * Gán giá trị mặc định khi tạo mới
     */
    protected $attributes = [
        'status' => 1,
        'sort_order' => 0,
    ];

    /**
     * Khi set name → nếu slug trống thì tự sinh slug
     */
    protected static function booted()
    {
        static::creating(function ($topic) {
            if (empty($topic->slug)) {
                $topic->slug = Str::slug($topic->name);
            }
        });

        static::updating(function ($topic) {
            // Nếu tên thay đổi và slug trống → cập nhật slug
            if (empty($topic->slug)) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }

    /**
     * (Tuỳ chọn) Liên kết tới user nếu bạn có bảng users
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
