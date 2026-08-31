<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(2_000, 60_000);

        return [
            'name' => ucfirst(fake()->words(2, true)),
            'price' => $price,
            'sell_price' => $price + fake()->numberBetween(1_000, 30_000),
            'extra_price' => fake()->numberBetween(500, 10_000),
            'image' => null,
            'discount' => 0,
            'current_stock' => fake()->numberBetween(10, 100),
            'company_id' => Company::factory(),
            'category_id' => ProductCategory::factory(),
            'code' => fake()->unique()->numberBetween(10_000, 99_999),
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn () => [
            'company_id' => $company->id,
            'category_id' => ProductCategory::factory()->for($company),
        ]);
    }
}
