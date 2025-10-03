<?php

namespace Database\Factories;

use App\Models\Presence;
use App\Models\Student;
use App\Models\MeetingSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PresenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Presence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'meeting_schedule_id' => MeetingSchedule::factory(),
            'status' => $this->faker->randomElement(['hadir', 'izin', 'sakit', 'alpha']),
            'description' => $this->faker->optional()->sentence(),
            'date' => $this->faker->date(),
            'user_id' => User::factory(),
        ];
    }
}
