<?php

namespace Database\Factories;

use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParentProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParentProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'nik' => $this->faker->unique()->numerify('################'),
            'kk' => $this->faker->unique()->numerify('################'),
            'gender' => $this->faker->randomElement(['L', 'P']),
            'parent_as' => $this->faker->randomElement(['ayah', 'ibu']),
            'card_address' => $this->faker->address(),
            'domicile_address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'occupation_id' => null,
            'education_id' => null,
            'user_id' => User::factory(),
            'photo' => null,
        ];
    }
}
