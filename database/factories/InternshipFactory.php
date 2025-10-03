<?php

namespace Database\Factories;

use App\Models\Internship;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\InternshipSupervisor;
use Illuminate\Database\Eloquent\Factories\Factory;

class InternshipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Internship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'student_id' => Student::factory(),
            'supervisor_id' => InternshipSupervisor::factory(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'file' => $this->faker->optional()->word() . '.pdf',
            'long_term' => $this->faker->optional()->numberBetween(1, 12),
        ];
    }
}
