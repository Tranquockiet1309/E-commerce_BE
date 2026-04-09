<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('topic')->insert([
            [
                'name' => 'Tin tức',
                'slug' => 'tin-tuc',
                'sort_order' => 1,
                'description' => 'Chuyên mục tin tức mới nhất',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Hướng dẫn',
                'slug' => 'huong-dan',
                'sort_order' => 2,
                'description' => 'Chuyên mục hướng dẫn sử dụng',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Khuyến mãi',
                'slug' => 'khuyen-mai',
                'sort_order' => 3,
                'description' => 'Chuyên mục khuyến mãi hấp dẫn',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
