<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'interest_rate' => $this->faker->randomFloat(2, 0, 10),
            'minimum_balance' => $this->faker->randomNumber(5),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
