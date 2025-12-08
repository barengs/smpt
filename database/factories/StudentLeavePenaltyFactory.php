<?php

namespace Database\Factories;

use App\Models\StudentLeavePenalty;
use App\Models\StudentLeave;
use App\Models\StudentLeaveReport;
use App\Models\Sanction;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentLeavePenaltyFactory extends Factory
{
    protected $model = StudentLeavePenalty::class;

    public function definition(): array
    {
        $penaltyType = fake()->randomElement(['peringatan', 'sanksi', 'poin']);
        $pointValue = $penaltyType === 'poin' ? fake()->numberBetween(5, 50) : 0;

        return [
            'student_leave_id' => StudentLeave::factory(),
            'student_leave_report_id' => StudentLeaveReport::factory(),
            'penalty_type' => $penaltyType,
            'description' => fake()->sentence(),
            'point_value' => $pointValue,
            'sanction_id' => $penaltyType === 'sanksi' ? Sanction::factory() : null,
            'assigned_by' => Staff::factory(),
            'assigned_at' => now(),
        ];
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'penalty_type' => 'peringatan',
            'point_value' => 0,
            'sanction_id' => null,
        ]);
    }

    public function withSanction(): static
    {
        return $this->state(fn (array $attributes) => [
            'penalty_type' => 'sanksi',
            'sanction_id' => Sanction::factory(),
        ]);
    }

    public function withPoints(): static
    {
        return $this->state(fn (array $attributes) => [
            'penalty_type' => 'poin',
            'point_value' => fake()->numberBetween(10, 50),
            'sanction_id' => null,
        ]);
    }
}
