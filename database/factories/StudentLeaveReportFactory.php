<?php

namespace Database\Factories;

use App\Models\StudentLeaveReport;
use App\Models\StudentLeave;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class StudentLeaveReportFactory extends Factory
{
    protected $model = StudentLeaveReport::class;

    public function definition(): array
    {
        $reportDate = fake()->dateTimeBetween('-1 month', 'now');
        $isLate = fake()->boolean(30);

        return [
            'student_leave_id' => StudentLeave::factory(),
            'report_date' => $reportDate,
            'report_time' => fake()->time('H:i'),
            'report_notes' => fake()->optional()->sentence(),
            'condition' => fake()->randomElement(['sehat', 'sakit', 'lainnya']),
            'is_late' => $isLate,
            'late_days' => $isLate ? fake()->numberBetween(1, 5) : 0,
            'reported_to' => Staff::factory(),
        ];
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_late' => true,
            'late_days' => fake()->numberBetween(1, 5),
        ]);
    }

    public function onTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_late' => false,
            'late_days' => 0,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now(),
            'verified_by' => Staff::factory(),
        ]);
    }
}
