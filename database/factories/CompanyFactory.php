<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'phone_number' => fake()->unique()->phoneNumber(),
            'user_id' => UserFactory::new(),
            'logo' => fake()->imageUrl(),
            'balance' => fake()->randomFloat(2, 0, 1000),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'open_time' => fake()->time(),
            'close_time' => fake()->time(),
        ];
    }
}
