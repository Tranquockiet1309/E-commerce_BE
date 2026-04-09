<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BannerSeeder::class,
            CategorySeeder::class,
            ContactSeeder::class,
            MenuSeeder::class,
            ProductSeeder::class,
            ProductImageSeeder::class,
            ProductSaleSeeder::class,
            ProductStoreSeeder::class,
            AttributeSeeder::class,
            ProductAttributeSeeder::class,
            TopicSeeder::class,
            PostSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
            OrderDetailSeeder::class,
            SettingSeeder::class,
        ]);

    }
}
