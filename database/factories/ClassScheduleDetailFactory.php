<?php

namespace Database\Factories;

use App\Models\ClassScheduleDetail;
use App\Models\ClassSchedule;
use App\Models\LessonHour;
use App\Models\Staff;
use App\Models\Study;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassScheduleDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClassScheduleDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_schedule_id' => ClassSchedule::factory(),
            'lesson_hour_id' => LessonHour::factory(),
            'teacher_id' => Staff::factory(),
            'study_id' => Study::factory(),
        ];
    }
}
