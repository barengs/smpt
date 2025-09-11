<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\Study;
use App\Models\StaffStudy;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StaffStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all staff members with the 'asatidz' role
        $teachers = Staff::whereHas('user', function ($query) {
            $query->role('asatidz');
        })->get();

        // Get all studies
        $studies = Study::all();

        // If we have teachers and studies, create some mappings
        if ($teachers->count() > 0 && $studies->count() > 0) {
            foreach ($teachers as $teacher) {
                // Assign random studies to each teacher (1-3 studies)
                $randomStudies = $studies->random(rand(1, min(3, $studies->count())));
                foreach ($randomStudies as $study) {
                    StaffStudy::firstOrCreate([
                        'staff_id' => $teacher->id,
                        'study_id' => $study->id,
                    ]);
                }
            }
        }
    }
}
