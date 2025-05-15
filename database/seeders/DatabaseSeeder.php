<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'phone_number' => '+998901234567',
            'password' => bcrypt('password'),// password
        ]);

        Company::factory()->create([
           'name' => 'Test Company',
           'phone_number' => '+998901234567',
            'logo' => asset('images/company/img.png'),
            'user_id' => 1,

        ]);


        ProductCategory::factory()
            ->count(5)
            ->create();

        // Create 50 products
        Product::factory()
            ->count(100)
            ->create();
    }
}
