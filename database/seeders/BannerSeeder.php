<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('banner')->insert([
            [
                'name' => 'Banner Khuyến Mãi',
                'image' => 'banners/banner1.jpg',
                'link' => 'https://example.com/khuyen-mai',
                'position' => 'slideshow',
                'sort_order' => 1,
                'description' => 'Giảm giá đặc biệt cho mùa hè!',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Banner Quảng Cáo',
                'image' => 'banners/banner2.jpg',
                'link' => 'https://example.com/ads',
                'position' => 'ads',
                'sort_order' => 2,
                'description' => 'Quảng cáo sản phẩm mới.',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
