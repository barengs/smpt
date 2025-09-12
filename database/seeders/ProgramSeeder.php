<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the programs to be seeded
        $programs = [
            'Tibyan',
            'Khuba'
        ];

        // Insert each program into the database
        foreach ($programs as $program) {
            Program::create([
                'name' => $program,
            ]);
        }
    }
}
