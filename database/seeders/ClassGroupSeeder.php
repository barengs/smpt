<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'IA',
                'classroom_id' => 1,
            ],
            [
                'name' => 'IB',
                'classroom_id' => 1,
            ],
            [
                'name' => 'IC',
                'classroom_id' => 1,
            ],
            [
                'name' => 'IIA',
                'classroom_id' => 2,
            ],
            [
                'name' => 'IIB',
                'classroom_id' => 2,
            ],
            [
                'name' => 'IIC',
                'classroom_id' => 2,
            ],
            [
                'name' => 'IIIA',
                'classroom_id' => 3,
            ],
        ];

        foreach ($data as $item) {
            ClassGroup::create($item);
        }
    }
}
