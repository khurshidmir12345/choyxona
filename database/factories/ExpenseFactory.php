<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentMethods = ['Naqd pul', 'Plastik karta', 'Bank o\'tkazmasi', 'Boshqa'];
        $statuses = ['pending', 'approved', 'rejected'];

        return [
            'company_id' => Company::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'amount' => $this->faker->randomFloat(2, 1000, 1000000),
            'expense_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'receipt_file' => null,
            'status' => $this->faker->randomElement($statuses),
        ];
    }
}
