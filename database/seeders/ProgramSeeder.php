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
            ['name' => 'Program Studi Informatika', 'description' => 'Program studi yang fokus pada ilmu komputer dan teknologi informasi'],
            ['name' => 'Program Studi Sistem Informasi', 'description' => 'Program studi yang fokus pada penerapan teknologi informasi dalam bisnis'],
            ['name' => 'Program Studi Teknik Komputer', 'description' => 'Program studi yang fokus pada rekayasa perangkat keras komputer'],
            ['name' => 'Program Studi Matematika', 'description' => 'Program studi yang fokus pada ilmu matematika dan aplikasinya'],
            ['name' => 'Program Studi Fisika', 'description' => 'Program studi yang fokus pada ilmu fisika dan aplikasinya'],
            ['name' => 'Program Studi Kimia', 'description' => 'Program studi yang fokus pada ilmu kimia dan aplikasinya'],
            ['name' => 'Program Studi Biologi', 'description' => 'Program studi yang fokus pada ilmu biologi dan aplikasinya'],
            ['name' => 'Program Studi Teknik Sipil', 'description' => 'Program studi yang fokus pada rekayasa infrastruktur sipil'],
            ['name' => 'Program Studi Teknik Mesin', 'description' => 'Program studi yang fokus pada rekayasa mesin dan manufaktur'],
            ['name' => 'Program Studi Teknik Elektro', 'description' => 'Program studi yang fokus pada rekayasa sistem kelistrikan'],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}
