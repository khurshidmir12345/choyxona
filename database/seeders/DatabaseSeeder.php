<?php

namespace Database\Seeders;

use App\Models\Company;
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
            'password' => 'password', // password
        ]);

        Company::factory()->create([
           'name' => 'Test Company',
           'phone_number' => '+998901234567',
            'logo' => asset('images/company/img.png'),
            'user_id' => 1,

        ]);
    }
}
