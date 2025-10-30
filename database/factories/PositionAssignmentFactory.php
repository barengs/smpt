<?php

namespace Database\Factories;

use App\Models\PositionAssignment;
use App\Models\Position;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionAssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PositionAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'position_id' => Position::factory(),
            'staff_id' => Staff::factory(),
            'start_date' => $this->faker->dateTimeThisYear(),
            'end_date' => null,
            'assignment_letter' => null,
            'notes' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
