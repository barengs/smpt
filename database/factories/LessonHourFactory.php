<?php

namespace Database\Factories;

use App\Models\LessonHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LessonHour>
 */
class LessonHourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->time('H:i');
        $endTime = date('H:i', strtotime($startTime) + 3600); // 1 hour after start time

        return [
            'name' => $this->faker->word() . ' Period',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'order' => $this->faker->numberBetween(1, 10),
            'description' => $this->faker->sentence(),
        ];
    }
}
