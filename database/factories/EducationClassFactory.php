<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EducationClass;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationClass>
 */
class EducationClassFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EducationClass::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->lexify('????'),
            'name' => $this->faker->word(),
        ];
    }
}
