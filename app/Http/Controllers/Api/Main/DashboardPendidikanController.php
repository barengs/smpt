<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\EducationalInstitution;
use App\Models\Classroom;
use App\Models\ClassGroup;

class DashboardPendidikanController extends Controller
{
    public function statistics(Request $request)
    {
        // 1. Total Program
        $totalPrograms = Program::count();
        
        // 2. Total Jenjang/Institusi Pendidikan
        $totalInstitutions = EducationalInstitution::count();
        
        // 3. Total Kelas (Tingkatan)
        $totalClassrooms = Classroom::count();
        
        // 4. Total Rombel
        $totalClassGroups = ClassGroup::count();
        
        // 5. Sebaran Rombel per Institusi
        $institutionDistribution = EducationalInstitution::withCount('classrooms')
            ->get()
            ->map(function($institution) {
                // To get total rombel for this institution, we can query ClassGroup
                $rombelCount = ClassGroup::where('educational_institution_id', $institution->id)->count();
                
                return [
                    'id' => $institution->id,
                    'name' => $institution->name,
                    'classroom_count' => $institution->classrooms_count,
                    'rombel_count' => $rombelCount
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_programs' => $totalPrograms,
                'total_institutions' => $totalInstitutions,
                'total_classrooms' => $totalClassrooms,
                'total_class_groups' => $totalClassGroups,
                'institution_distribution' => $institutionDistribution
            ]
        ]);
    }
}
