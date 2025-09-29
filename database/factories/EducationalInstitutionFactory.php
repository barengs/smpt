<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EducationalInstitution;
use App\Models\Education;
use App\Models\EducationClass;
use App\Models\Staff;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationalInstitution>
 */
class EducationalInstitutionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EducationalInstitution::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'education_id' => Education::factory(),
            'education_class_id' => EducationClass::factory(),
            'registration_number' => $this->faker->unique()->numerify('REG-####'),
            'institution_name' => $this->faker->company(),
            'institution_address' => $this->faker->address(),
            'institution_phone' => $this->faker->phoneNumber(),
            'institution_email' => $this->faker->unique()->companyEmail(),
            'institution_website' => $this->faker->url(),
            'institution_logo' => $this->faker->imageUrl(200, 200, 'business'),
            'institution_banner' => $this->faker->imageUrl(800, 200, 'business'),
            'institution_status' => $this->faker->randomElement(['active', 'inactive']),
            'institution_description' => $this->faker->paragraph(),
            'headmaster_id' => Staff::factory(),
        ];
    }
}
