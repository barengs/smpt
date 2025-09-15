<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AcademicYear;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AcademicYear::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = $this->faker->year();
        $endYear = $startYear + 1;

        return [
            'year' => $startYear . '/' . $endYear,
            'active' => $this->faker->boolean(20), // 20% chance of being active
            'description' => $this->faker->sentence(),
        ];
    }
}
