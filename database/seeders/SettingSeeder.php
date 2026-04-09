<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('setting')->insert([
            [
                'site_name' => 'Công ty TNHH ABC',
                'email' => 'info@abc.com',
                'phone' => '02812345678',
                'hotline' => '19001234',
                'address' => '123 Đường XYZ, Quận 1, TP.HCM',
                'status' => 1,
            ],
        ]);
    }
}
