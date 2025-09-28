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
        $year = fake()->year();
        $startDate = fake()->date('Y-m-d', '2025-06-01');
        $endDate = fake()->date('Y-m-d', '2026-05-31');

        return [
            'year' => $year . '/' . ($year + 1),
            'type' => fake()->randomElement(['semester', 'triwulan']),
            'periode' => fake()->randomElement(['ganjil', 'genap', 'pendek', 'cawu 1', 'cawu 2', 'cawu 3']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'active' => fake()->boolean(),
            'description' => fake()->sentence(),
        ];
    }
}
