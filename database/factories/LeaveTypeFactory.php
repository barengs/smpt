<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Pulang', 'Keluar Pesantren', 'Sakit', 'Berobat',
                'Keperluan Keluarga', 'Keperluan Pribadi', 'Mengikuti Kegiatan'
            ]),
            'description' => fake()->sentence(10),
            'requires_approval' => fake()->boolean(80),
            'max_duration_days' => fake()->randomElement([3, 5, 7, 14]),
            'is_active' => fake()->boolean(90),
        ];
    }
}
