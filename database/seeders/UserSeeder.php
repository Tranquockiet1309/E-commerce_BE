<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'phone' => '0900000001',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'roles' => 'admin',
                'avatar' => null,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Nguyễn Văn A',
                'email' => 'vana@example.com',
                'phone' => '0900000002',
                'username' => 'vana',
                'password' => Hash::make('vana123'),
                'roles' => 'customer',
                'avatar' => null,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
            [
                'name' => 'Trần Thị B',
                'email' => 'thib@example.com',
                'phone' => '0900000003',
                'username' => 'thib',
                'password' => Hash::make('thib123'),
                'roles' => 'customer',
                'avatar' => null,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'updated_at' => null,
                'updated_by' => null,
                'status' => 1,
            ],
        ]);
    }
}
