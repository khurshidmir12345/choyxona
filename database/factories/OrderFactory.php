<?php

namespace Database\Factories;

use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => 2, // Fixed company_id as requested
            'place_id' => Place::inRandomOrder()->first()->id, // Randomly select one of the 2 places
            'user_id' => 2, // Fixed user_id as requested
            'total_amount' => $this->faker->numberBetween(1000, 100000),
            'discount' => $this->faker->numberBetween(0, 5000),
            'type' => $this->faker->randomElement([\App\Casts\OrderTypeEnum::Cafe->value]),
            'status' => $this->faker->randomElement([\App\Casts\OrderStatusEnum::Done->value]),
        ];
    }
}
