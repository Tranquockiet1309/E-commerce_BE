<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class OrderdetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('orderdetail')->insert([
            [
                'order_id' => 1,
                'product_id' => 1,
                'price' => 15000000.00,
                'qty' => 1,
                'amount' => 15000000.00,
                'discount' => 500000.00,
            ],
            [
                'order_id' => 1,
                'product_id' => 2,
                'price' => 20000000.00,
                'qty' => 2,
                'amount' => 40000000.00,
                'discount' => 1000000.00,
            ],
            [
                'order_id' => 2,
                'product_id' => 3,
                'price' => 500000.00,
                'qty' => 3,
                'amount' => 1500000.00,
                'discount' => 0.00,
            ],
        ]);
    }
}
