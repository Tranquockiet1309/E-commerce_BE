<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_sale')->insert([
            [
                'name' => 'Khuyến mãi điện thoại',
                'product_id' => 1,
                'price_sale' => 14000000.00,
                'date_begin' => Carbon::now()->subDays(5),
                'date_end' => Carbon::now()->addDays(10),
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Giảm giá laptop',
                'product_id' => 2,
                'price_sale' => 11500000.00,
                'date_begin' => Carbon::now()->subDays(2),
                'date_end' => Carbon::now()->addDays(7),
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
