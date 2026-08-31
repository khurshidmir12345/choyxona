<?php

namespace Database\Factories;

use App\Casts\PlaceStatusEnum;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->numberBetween(1, 99).'-so\'ri',
            'company_id' => Company::factory(),
            'status' => PlaceStatusEnum::Empty,
            'capacity' => fake()->numberBetween(2, 12),
        ];
    }
}
