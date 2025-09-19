<?php

namespace Database\Factories;

use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\Education;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentClassFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentClass::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'education_id' => Education::factory(),
            'student_id' => Student::factory(),
            'class_id' => Classroom::factory(),
            'approval_status' => $this->faker->randomElement(['diajukan', 'disetujui', 'ditolak']),
            'approval_note' => $this->faker->optional()->sentence(),
            'approved_by' => User::factory(),
        ];
    }
}
