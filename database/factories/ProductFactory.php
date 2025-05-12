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
        return [
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 8000, 80000),
            'sell_price' => $this->faker->randomFloat(2, 10000, 100000),
            'extra_price' => $this->faker->randomFloat(2, 2000, 10000),
            'image' => asset('storage/products/dx90uwvSLhsZi6jC5jR1v6wAHoiVb6mAPUns1tAT.jpg'),
            'discount' => $this->faker->numberBetween(0, 30),
            'current_stock' => $this->faker->numberBetween(0, 100),
            'company_id' => 2,
            'category_id' => $this->faker->numberBetween(159, 161),
            'code' => $this->faker->unique()->numberBetween(11111, 99999),
        ];
    }
}
