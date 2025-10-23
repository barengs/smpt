<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Student;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'account_number' => $this->faker->unique()->numerify('##########'),
            'customer_id' => Student::factory(),
            'product_id' => Product::factory(),
            'balance' => $this->faker->randomNumber(5),
            'status' => $this->faker->randomElement(['AKTIF', 'TIDAK AKTIF', 'TUTUP', 'TERBLOKIR', 'DIBEKUKAN']),
            'open_date' => $this->faker->dateTimeThisYear(),
            'close_date' => null,
        ];
    }
}
