<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('category')->insert([
            [
                'name' => 'Điện thoại',
                'slug' => 'dien-thoai',
                'image' => 'categories/dien-thoai.jpg',
                'parent_id' => 0,
                'sort_order' => 1,
                'description' => 'Các loại điện thoại mới nhất.',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Laptop',
                'slug' => 'laptop',
                'image' => 'categories/laptop.jpg',
                'parent_id' => 0,
                'sort_order' => 2,
                'description' => 'Laptop cho mọi nhu cầu.',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Phụ kiện',
                'slug' => 'phu-kien',
                'image' => 'categories/phu-kien.jpg',
                'parent_id' => 0,
                'sort_order' => 3,
                'description' => 'Phụ kiện công nghệ.',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
