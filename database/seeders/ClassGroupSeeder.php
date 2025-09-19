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
                'name' => 'XA',
                'classroom_id' => 1,
            ],
            [
                'name' => 'XB',
                'classroom_id' => 1,
            ],
            [
                'name' => 'XC',
                'classroom_id' => 1,
            ],
            [
                'name' => 'XIA',
                'classroom_id' => 2,
            ],
            [
                'name' => 'XIB',
                'classroom_id' => 2,
            ],
            [
                'name' => 'XIC',
                'classroom_id' => 2,
            ],
            [
                'name' => 'XIIA',
                'classroom_id' => 3,
            ],
        ];

        foreach ($data as $item) {
            ClassGroup::create($item);
        }
    }
}
