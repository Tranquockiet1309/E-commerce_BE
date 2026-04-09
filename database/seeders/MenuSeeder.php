<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menu')->insert([
            [
                'name' => 'Trang chủ',
                'link' => '/',
                'type' => 'custom',
                'parent_id' => 0,
                'sort_order' => 1,
                'table_id' => null,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Danh mục sản phẩm',
                'link' => '/danh-muc',
                'type' => 'category',
                'parent_id' => 0,
                'sort_order' => 2,
                'table_id' => 1,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Tin tức',
                'link' => '/tin-tuc',
                'type' => 'topic',
                'parent_id' => 0,
                'sort_order' => 3,
                'table_id' => 1,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Giới thiệu',
                'link' => '/gioi-thieu',
                'type' => 'page',
                'parent_id' => 0,
                'sort_order' => 4,
                'table_id' => 1,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
