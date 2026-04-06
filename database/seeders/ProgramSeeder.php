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
        $programs = [
            'Tibyan',
            'Kubar'
        ];

        // Insert each program into the database using updateOrCreate for idempotency
        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['name' => $program === 'Kubar' ? 'Khuba' : $program], // Find existing Khuba if it exists
                ['name' => $program]
            );
        }
    }
}
