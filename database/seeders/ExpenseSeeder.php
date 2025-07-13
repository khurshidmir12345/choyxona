<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::find(2);
        $user = User::find(2);
        
        if (!$company || !$user) {
            return;
        }
        
        $categories = ExpenseCategory::where('company_id', $company->id)->get();
        
        if ($categories->isEmpty()) {
            return;
        }
        
        // Create 30-50 sample expenses for company_id = 2
        $expenseCount = rand(30, 50);
        
        for ($i = 0; $i < $expenseCount; $i++) {
            Expense::create([
                'company_id' => $company->id,
                'expense_category_id' => $categories->random()->id,
                'user_id' => $user->id,
                'title' => $this->getRandomExpenseTitle(),
                'description' => fake()->paragraph(),
                'amount' => rand(10000, 5000000), // 10,000 - 5,000,000 UZS
                'expense_date' => fake()->dateTimeBetween('-6 months', 'now'),
                'payment_method' => fake()->randomElement(['Naqd pul', 'Plastik karta', 'Bank o\'tkazmasi', 'Boshqa']),
                'receipt_file' => null,
                'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            ]);
        }
    }
    
    private function getRandomExpenseTitle(): string
    {
        $titles = [
            'Elektron to\'lovi',
            'Suv to\'lovi',
            'Gaz to\'lovi',
            'Xodim ish haqi',
            'Bonus to\'lovi',
            'Kofe xom ashyosi',
            'Shakar xom ashyosi',
            'Sut xom ashyosi',
            'Kompyuter jihozi',
            'Printer jihozi',
            'Stol va stullar',
            'Reklama xizmati',
            'SMM xizmati',
            'Yuk tashish xizmati',
            'Taksi xizmati',
            'Ofis materiallari',
            'Tozalash xizmati',
            'Xavfsizlik xizmati',
            'Internet xizmati',
            'Telefon xizmati',
        ];
        
        return fake()->randomElement($titles);
    }
}
