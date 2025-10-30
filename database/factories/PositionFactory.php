<?php

namespace Database\Factories;

use App\Models\Position;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->jobTitle(),
            'code' => $this->faker->unique()->lexify('POS???'),
            'description' => $this->faker->sentence(),
            'organization_id' => Organization::factory(),
            'parent_id' => null,
            'level' => 1,
            'is_active' => true,
        ];
    }
}
