<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('order')->insert([
            [
                'user_id' => 1,
                'name' => 'Nguyễn Văn A',
                'email' => 'vana@example.com',
                'phone' => '0901234567',
                'address' => '123 Đường ABC, Quận 1, TP.HCM',
                'note' => 'Giao hàng trong giờ hành chính',
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'user_id' => 2,
                'name' => 'Trần Thị B',
                'email' => 'thib@example.com',
                'phone' => '0912345678',
                'address' => '456 Đường XYZ, Quận 3, TP.HCM',
                'note' => null,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
