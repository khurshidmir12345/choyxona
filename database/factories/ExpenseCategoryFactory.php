<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Kommunal to\'lovlar' => '#ef4444',
            'Ish haqi' => '#3b82f6',
            'Xom ashyo' => '#10b981',
            'Uskuna va jihozlar' => '#f59e0b',
            'Marketing' => '#8b5cf6',
            'Transport' => '#06b6d4',
            'Boshqa xarajatlar' => '#6b7280',
        ];

        $category = $this->faker->unique()->randomElement(array_keys($categories));
        $color = $categories[$category];

        return [
            'company_id' => Company::factory(),
            'name' => $category,
            'description' => $this->faker->sentence(),
            'color' => $color,
            'is_active' => true,
        ];
    }
}
