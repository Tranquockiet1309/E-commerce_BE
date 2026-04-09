<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contact')->insert([
            [
                'user_id' => null,
                'name' => 'Nguyễn Văn A',
                'email' => 'vana@example.com',
                'phone' => '0901234567',
                'content' => 'Tôi muốn hỏi về sản phẩm điện thoại.',
                'reply_id' => 0,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'user_id' => null,
                'name' => 'Trần Thị B',
                'email' => 'thib@example.com',
                'phone' => '0912345678',
                'content' => 'Shop có hỗ trợ bảo hành không?',
                'reply_id' => 0,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
