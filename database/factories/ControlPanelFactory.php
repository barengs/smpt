<?php

namespace Database\Factories;

use App\Models\ControlPanel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ControlPanel>
 */
class ControlPanelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'app_name' => $this->faker->company() . ' Application',
            'app_version' => '1.0.' . $this->faker->numberBetween(0, 9),
            'app_description' => $this->faker->sentence(),
            'app_logo' => null,
            'app_favicon' => null,
            'app_url' => $this->faker->url(),
            'app_email' => $this->faker->companyEmail(),
            'app_phone' => $this->faker->phoneNumber(),
            'app_address' => $this->faker->address(),
            'is_maintenance_mode' => $this->faker->randomElement(['true', 'false']),
            'maintenance_message' => $this->faker->optional()->sentence(),
            'app_theme' => $this->faker->randomElement(['light', 'dark', 'system']),
            'app_language' => $this->faker->randomElement(['indonesia', 'english', 'arabic']),
        ];
    }
}
