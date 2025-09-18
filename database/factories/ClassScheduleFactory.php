<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\AcademicYear;
use App\Models\Education;
use App\Models\Classroom;
use App\Models\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClassSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'education_id' => Education::factory(),
            'classroom_id' => Classroom::factory(),
            'class_group_id' => ClassGroup::factory(),
            'day' => $this->faker->randomElement(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu']),
            'session' => $this->faker->randomElement(['pagi', 'sore', 'siang', 'malam']),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
