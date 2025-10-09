<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use GuzzleHttp\Promise\Create;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicYear::create([
            'year' => '1446/1447',
            'type' => 'semester',
            'periode' => 'ganjil',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
            'status' => true,
        ]);
    }
}
