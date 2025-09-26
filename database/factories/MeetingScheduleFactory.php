<?php

namespace Database\Factories;

use App\Models\MeetingSchedule;
use App\Models\ClassScheduleDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MeetingSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_schedule_detail_id' => ClassScheduleDetail::factory(),
            'meeting_sequence' => $this->faker->numberBetween(1, 10),
            'meeting_date' => $this->faker->date(),
            'topic' => $this->faker->sentence(3),
        ];
    }
}
