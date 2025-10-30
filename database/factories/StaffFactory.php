<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Staff;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => $this->faker->unique()->lexify('STF???'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'nik' => $this->faker->unique()->numerify('####################'),
            'nip' => $this->faker->unique()->numerify('####################'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'zip_code' => $this->faker->postcode(),
            'photo' => $this->faker->imageUrl(),
            'marital_status' => $this->faker->randomElement(['Belum Menikah', 'Menikah', 'Duda', 'Janda']),
            'status' => $this->faker->randomElement(['Aktif', 'Tidak Aktif']),
            'birth_date' => $this->faker->date(),
            'birth_place' => $this->faker->city(),
            'gender' => $this->faker->randomElement(['Pria', 'Wanita', 'L', 'P']),
        ];
    }
}
