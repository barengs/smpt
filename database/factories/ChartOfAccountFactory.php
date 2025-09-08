<?php

namespace Database\Factories;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChartOfAccount>
 */
class ChartOfAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coa_code' => $this->faker->unique()->numerify('####'),
            'account_name' => $this->faker->word(),
            'account_type' => $this->faker->randomElement(['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE']),
            'parent_coa_code' => null,
            'level' => $this->faker->randomElement(['header', 'subheader', 'detail']),
            'is_postable' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}
