<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'phone_number' => fake()->unique()->numerify('+9989########'),
            'user_id' => UserFactory::new(),
            'logo' => null,
            'balance' => 0,
            'open_time' => '09:00',
            'close_time' => '23:00',
        ];
    }
}
