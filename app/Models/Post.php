<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Post extends Model
{
    protected $table = 'post';   // nếu bảng tên "post" (không phải "posts")

    protected $fillable = [
        'topic_id',
        'title',
        'slug',
        'image',
        'content',
        'description',
        'post_type',
        'created_by',
        'updated_by',
        'status',
    ];
}

