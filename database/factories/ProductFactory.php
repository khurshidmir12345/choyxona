<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::inRandomOrder()->first();
        $category = ProductCategory::inRandomOrder()->first();

        return [
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 1000, 100000),
            'sell_price' => $this->faker->randomFloat(2, 1000, 100000),
            'extra_price' => $this->faker->randomFloat(2, 500, 10000),
            'image' => $this->faker->imageUrl(),
            'discount' => 5,
            'current_stock' => rand(10, 100),
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => $this->faker->unique()->randomNumber(5),
        ];
    }
}
