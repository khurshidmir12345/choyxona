<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ExpenseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kommunal to\'lovlar',
                'description' => 'Elektron, suv, gaz va boshqa kommunal xizmatlar',
                'color' => '#ef4444',
            ],
            [
                'name' => 'Ish haqi',
                'description' => 'Xodimlar ish haqi va bonuslar',
                'color' => '#3b82f6',
            ],
            [
                'name' => 'Xom ashyo',
                'description' => 'Mahsulot ishlab chiqarish uchun xom ashyo',
                'color' => '#10b981',
            ],
            [
                'name' => 'Uskuna va jihozlar',
                'description' => 'Ofis va ishlab chiqarish jihozlari',
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Marketing',
                'description' => 'Reklama va marketing xarajatlari',
                'color' => '#8b5cf6',
            ],
            [
                'name' => 'Transport',
                'description' => 'Yuk tashish va transport xarajatlari',
                'color' => '#06b6d4',
            ],
            [
                'name' => 'Boshqa xarajatlar',
                'description' => 'Boshqa turdagi xarajatlar',
                'color' => '#6b7280',
            ],
        ];

        // Create categories only for company_id = 2
        $company = Company::find(2);
        
        if ($company) {
            foreach ($categories as $category) {
                ExpenseCategory::create([
                    'company_id' => $company->id,
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'color' => $category['color'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
