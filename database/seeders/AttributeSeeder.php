<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('attribute')->insert([
            ['name' => 'Màu sắc'],
            ['name' => 'Kích thước'],
            ['name' => 'Chất liệu'],
            ['name' => 'Xuất xứ'],
            ['name' => 'Thương hiệu'],
        ]);
    }
}
