<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderDetail>
 */
class OrderDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::inRandomOrder()->first()->id, // Creates a new Order if not specified
            'product_id' => Product::factory(), // Assumes Product factory exists
            'worker_id' => 2, // Assumes worker is a User
            'discount' => $this->faker->numberBetween(0, 20),
            'quantity' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->numberBetween(500, 50000),
        ];
    }
}
