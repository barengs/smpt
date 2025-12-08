<?php

namespace Database\Factories;

use App\Models\StudentLeave;
use App\Models\Student;
use App\Models\LeaveType;
use App\Models\AcademicYear;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class StudentLeaveFactory extends Factory
{
    protected $model = StudentLeave::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-3 months', '+1 month');
        $durationDays = fake()->numberBetween(1, 7);
        $endDate = Carbon::instance($startDate)->addDays($durationDays - 1);

        return [
            'student_id' => Student::factory(),
            'leave_type_id' => LeaveType::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
            'reason' => fake()->paragraph(2),
            'destination' => fake()->address(),
            'contact_person' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'active', 'completed', 'overdue']),
            'expected_return_date' => Carbon::instance($endDate)->addDay(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => Staff::factory(),
            'approved_at' => now(),
            'approval_notes' => fake()->optional()->sentence(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => Staff::factory(),
            'approved_at' => now(),
            'approval_notes' => fake()->sentence(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'approved_by' => Staff::factory(),
            'approved_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'approved_by' => Staff::factory(),
            'approved_at' => now(),
            'actual_return_date' => Carbon::instance($attributes['end_date'])->addDay(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'approved_by' => Staff::factory(),
            'approved_at' => now(),
            'has_penalty' => true,
            'actual_return_date' => Carbon::instance($attributes['end_date'])->addDays(fake()->numberBetween(2, 5)),
        ]);
    }
}
