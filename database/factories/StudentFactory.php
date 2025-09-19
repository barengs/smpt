<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;
use App\Models\Program;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => fake()->numerify('################'),
            'nis' => fake()->unique()->numerify('######'),
            'nik' => fake()->unique()->numerify('################'),
            'kk' => fake()->unique()->numerify('################'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['L', 'P']),
            'address' => fake()->address(),
            'born_in' => fake()->city(),
            'born_at' => fake()->date(),
            'program_id' => Program::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
