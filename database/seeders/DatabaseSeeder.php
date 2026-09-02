<?php

namespace Database\Seeders;

use App\Casts\PlaceStatusEnum;
use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\Place;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Bo'sh bazani ishlaydigan holatga keltiradi: bitta choyxona,
     * uning egasi, menyu va zal joylari.
     */
    public function run(): void
    {
        foreach (['director', 'kassir', 'ofitsiant'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $owner = User::firstOrCreate(
            ['phone_number' => '+998901234567'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'phone_verified_at' => now(),
                'role_id' => Role::where('name', 'director')->value('id'),
            ],
        );

        $company = Company::firstOrCreate(
            ['user_id' => $owner->id],
            [
                'name' => 'Choyxona',
                'phone_number' => '+998901234567',
                'address' => 'Toshkent',
                'open_time' => '09:00',
                'close_time' => '23:00',
            ],
        );

        $menu = [
            'Choy va ichimliklar' => [
                ['Ko\'k choy', 4_000, 8_000],
                ['Qora choy', 4_000, 8_000],
                ['Limonli choy', 6_000, 12_000],
                ['Ayron', 5_000, 10_000],
            ],
            'Issiq taomlar' => [
                ['Osh', 22_000, 38_000],
                ['Shurva', 18_000, 32_000],
                ['Lag\'mon', 20_000, 35_000],
                ['Manti (5 dona)', 18_000, 34_000],
            ],
            'Salatlar' => [
                ['Achchiq-chuchuk', 6_000, 14_000],
                ['Sezar', 14_000, 28_000],
            ],
            'Nonushta' => [
                ['Somsa', 8_000, 15_000],
                ['Non', 2_000, 4_000],
            ],
        ];

        foreach ($menu as $categoryName => $products) {
            $category = ProductCategory::firstOrCreate([
                'company_id' => $company->id,
                'name' => $categoryName,
            ]);

            foreach ($products as [$name, $cost, $price]) {
                Product::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $name],
                    [
                        'category_id' => $category->id,
                        'price' => $cost,
                        'sell_price' => $price,
                        'extra_price' => $price - $cost,
                        'discount' => 0,
                        'current_stock' => 100,
                    ],
                );
            }
        }

        foreach (['1-so\'ri' => 8, '2-so\'ri' => 8, '3-so\'ri' => 6, '1-stol' => 4, '2-stol' => 4, 'VIP xona' => 12] as $name => $capacity) {
            Place::firstOrCreate(
                ['company_id' => $company->id, 'name' => $name],
                ['capacity' => $capacity, 'status' => PlaceStatusEnum::Empty],
            );
        }

        foreach (['Xomashyo' => '#12866f', 'Kommunal' => '#f59e0b', 'Ish haqi' => '#3b82f6', 'Boshqa' => '#64748b'] as $name => $color) {
            ExpenseCategory::firstOrCreate(
                ['company_id' => $company->id, 'name' => $name],
                ['color' => $color, 'is_active' => true],
            );
        }
    }
}
