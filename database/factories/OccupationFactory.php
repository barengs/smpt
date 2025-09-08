<?php

namespace Database\Factories;

use App\Models\Occupation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Occupation>
 */
class OccupationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->lexify('???###'),
            'name' => $this->faker->unique()->jobTitle(),
            'description' => $this->faker->sentence(),
        ];
    }
}
