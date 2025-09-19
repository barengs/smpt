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
            ['name' => 'X'],
            ['name' => 'XI'],
            ['name' => 'XII'],
        ];

        foreach ($data as $item) {
            Classroom::create($item);
        }
    }
}
