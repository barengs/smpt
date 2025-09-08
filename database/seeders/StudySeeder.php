<?php

namespace Database\Seeders;

use App\Models\Study;
use Illuminate\Database\Seeder;

class StudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studies = [
            ['name' => 'Studi Ilmu Komputer', 'description' => 'Studi yang fokus pada ilmu komputer dan teknologi informasi'],
            ['name' => 'Studi Sistem Informasi', 'description' => 'Studi yang fokus pada penerapan teknologi informasi dalam bisnis'],
            ['name' => 'Studi Teknik Komputer', 'description' => 'Studi yang fokus pada rekayasa perangkat keras komputer'],
            ['name' => 'Studi Matematika', 'description' => 'Studi yang fokus pada ilmu matematika dan aplikasinya'],
            ['name' => 'Studi Fisika', 'description' => 'Studi yang fokus pada ilmu fisika dan aplikasinya'],
            ['name' => 'Studi Kimia', 'description' => 'Studi yang fokus pada ilmu kimia dan aplikasinya'],
            ['name' => 'Studi Biologi', 'description' => 'Studi yang fokus pada ilmu biologi dan aplikasinya'],
            ['name' => 'Studi Teknik Sipil', 'description' => 'Studi yang fokus pada rekayasa infrastruktur sipil'],
            ['name' => 'Studi Teknik Mesin', 'description' => 'Studi yang fokus pada rekayasa mesin dan manufaktur'],
            ['name' => 'Studi Teknik Elektro', 'description' => 'Studi yang fokus pada rekayasa sistem kelistrikan'],
        ];

        foreach ($studies as $study) {
            Study::create($study);
        }
    }
}
