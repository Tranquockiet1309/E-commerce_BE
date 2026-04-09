<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_attribute')->insert([
            [
                'product_id' => 1,
                'attribute_id' => 1,
                'value' => 'Đỏ',
            ],
            [
                'product_id' => 1,
                'attribute_id' => 2,
                'value' => '128GB',
            ],
            [
                'product_id' => 2,
                'attribute_id' => 1,
                'value' => 'Xanh',
            ],
            [
                'product_id' => 2,
                'attribute_id' => 2,
                'value' => '256GB',
            ],
        ]);
    }
}
