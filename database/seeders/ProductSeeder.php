<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product')->insert([
            [
                'category_id' => 1,
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'thumbnail' => 'iphone15pro.jpg',
                'content' => 'Điện thoại iPhone 15 Pro mới nhất với nhiều tính năng vượt trội.',
                'description' => 'Điện thoại cao cấp của Apple.',
                'price_buy' => 25000000.00,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'category_id' => 2,
                'name' => 'Laptop Dell XPS 13',
                'slug' => 'laptop-dell-xps-13',
                'thumbnail' => 'dellxps13.jpg',
                'content' => 'Laptop Dell XPS 13 với thiết kế mỏng nhẹ, hiệu năng mạnh mẽ.',
                'description' => 'Laptop dành cho doanh nhân.',
                'price_buy' => 32000000.00,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'category_id' => 3,
                'name' => 'Tai nghe Bluetooth',
                'slug' => 'tai-nghe-bluetooth',
                'thumbnail' => 'tainghe.jpg',
                'content' => 'Tai nghe Bluetooth chất lượng cao, kết nối ổn định.',
                'description' => 'Phụ kiện công nghệ.',
                'price_buy' => 1500000.00,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
