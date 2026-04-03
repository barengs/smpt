<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AcademicQuarter;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ay = AcademicYear::create([
            'year' => '1446/1447',
            'start_date' => '2025-08-01',
            'end_date' => '2026-07-31',
            'active' => true,
        ]);

        // Seed default quarters for the academic year with required dates
        $quarters = [
            [
                'name' => 'Kuartal 1', 
                'start_date' => '2025-08-01', 
                'end_date' => '2025-10-31', 
                'active' => true
            ],
            [
                'name' => 'Kuartal 2', 
                'start_date' => '2025-11-01', 
                'end_date' => '2026-01-31', 
                'active' => false
            ],
            [
                'name' => 'Kuartal 3', 
                'start_date' => '2026-02-01', 
                'end_date' => '2026-04-30', 
                'active' => false
            ],
            [
                'name' => 'Kuartal 4', 
                'start_date' => '2026-05-01', 
                'end_date' => '2026-07-31', 
                'active' => false
            ],
        ];

        foreach ($quarters as $q) {
            AcademicQuarter::create(array_merge($q, [
                'academic_year_id' => $ay->id
            ]));
        }
    }
}
