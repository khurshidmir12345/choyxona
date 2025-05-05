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
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'sell_price' => $this->faker->randomFloat(2, 8, 900),
            'extra_price' => $this->faker->randomFloat(2, 0, 200),
            'image' => $this->faker->imageUrl(640, 480, 'products'),
            'discount' => $this->faker->numberBetween(0, 50),
            'current_stock' => $this->faker->numberBetween(0, 100),
            'company_id' => Company::factory(),
            'category_id' => ProductCategory::factory(),
        ];
    }
}
