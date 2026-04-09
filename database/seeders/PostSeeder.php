<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('post')->insert([
            [
                'topic_id' => 1,
                'title' => 'Giới thiệu sản phẩm mới',
                'slug' => 'gioi-thieu-san-pham-moi',
                'image' => 'product_new.jpg',
                'content' => 'Nội dung chi tiết về sản phẩm mới.',
                'description' => 'Bài viết giới thiệu sản phẩm mới.',
                'created_at' => Carbon::now(),
                'post_type' => 'post',
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'topic_id' => 2,
                'title' => 'Hướng dẫn sử dụng',
                'slug' => 'huong-dan-su-dung',
                'image' => 'guide.jpg',
                'content' => 'Nội dung hướng dẫn sử dụng sản phẩm.',
                'description' => 'Bài viết hướng dẫn sử dụng.',
                'created_at' => Carbon::now(),
                'post_type' => 'post',
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'topic_id' => null,
                'title' => 'Trang liên hệ',
                'slug' => 'lien-he',
                'image' => 'contact.jpg',
                'content' => 'Thông tin liên hệ công ty.',
                'description' => 'Trang liên hệ.',
                'created_at' => Carbon::now(),
                'post_type' => 'page',
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
