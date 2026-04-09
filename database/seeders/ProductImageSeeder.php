<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_image')->insert([
            [
                'product_id' => 1,
                'image' => 'phone1_1.jpg',
                'alt' => 'Ảnh điện thoại 1 - góc nghiêng',
                'title' => 'Điện thoại mẫu 1',
            ],
            [
                'product_id' => 1,
                'image' => 'phone1_2.jpg',
                'alt' => 'Ảnh điện thoại 1 - mặt trước',
                'title' => 'Điện thoại mẫu 1 mặt trước',
            ],
            [
                'product_id' => 2,
                'image' => 'laptop1_1.jpg',
                'alt' => 'Ảnh laptop 1 - góc nghiêng',
                'title' => 'Laptop mẫu 1',
            ],
            [
                'product_id' => 2,
                'image' => 'laptop1_2.jpg',
                'alt' => 'Ảnh laptop 1 - bàn phím',
                'title' => 'Laptop mẫu 1 bàn phím',
            ],
        ]);
    }
}
