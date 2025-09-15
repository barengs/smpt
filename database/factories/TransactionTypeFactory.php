<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionType>
 */
class TransactionTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->lexify('TT???'),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'category' => $this->faker->randomElement(['transfer', 'payment', 'cash_operation', 'fee']),
            'is_debit' => $this->faker->boolean,
            'is_credit' => $this->faker->boolean,
            'default_debit_coa' => $this->faker->lexify('COA???'),
            'default_credit_coa' => $this->faker->lexify('COA???'),
            'is_active' => true,
        ];
    }
}
