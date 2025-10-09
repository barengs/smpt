<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'I'], // kelas 1 (satu sd)
            ['name' => 'II'],
            ['name' => 'III'],
            ['name' => 'IV'],
            ['name' => 'V'], // kelas 5 (lima sd)
            ['name' => 'VI'], // kelas 6 (enam sd)
            ['name' => 'VII'], // kelas 7 (satu smp)
            ['name' => 'VIII'], // kelas 8 (dua smp)
            ['name' => 'IX'], // kelas 9 (tiga smp)
            ['name' => 'X'], // kelas 10 (satu sma)
            ['name' => 'XI'], // kelas 11 (dua sma)
            ['name' => 'XII'], // kelas 12 (tiga sma)
        ];

        foreach ($data as $item) {
            Classroom::create($item);
        }
    }
}
