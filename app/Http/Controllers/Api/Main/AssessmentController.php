<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssessmentFormula;
use App\Models\StudentAssessment;
use App\Models\AssessmentScore;
use App\Models\ClassScheduleDetail;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * Get list of class schedule details that need assessment
     */
    public function index(Request $request)
    {
        // Simple return for boilerplate, frontend uses class-schedule endpoint
        return response()->json([
            'status' => 'success',
            'message' => 'List of assessments',
            'data' => []
        ]);
    }

    /**
     * Get specific class schedule detail with students and assessments
     */
    public function show($detailId, Request $request)
    {
        $semester = $request->query('semester', '1');
        
        $detail = ClassScheduleDetail::with([
            'study', 'teacher', 'classGroup', 'classroom',
            'classSchedule.academicYear'
        ])->findOrFail($detailId);

        // Get students built from StudentClass
        $classGroupId = $detail->class_group_id;
        $academicYearId = $detail->classSchedule->academic_year_id;

        $studentClasses = StudentClass::with('student')
            ->where('class_group_id', $classGroupId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->get();

        $students = $studentClasses->pluck('student');

        // Get existing assessments
        $assessments = StudentAssessment::with('assessmentScores')
            ->where('class_schedule_detail_id', $detailId)
            ->where('semester', $semester)
            ->get()
            ->keyBy('student_id');

        return response()->json([
            'status' => 'success',
            'message' => 'Assessment detail retrieved',
            'data' => [
                'detail' => $detail,
                'students' => $students,
                'assessments' => $assessments
            ]
        ]);
    }

    /**
     * Save or update assessment formula
     */
    public function saveFormula(Request $request)
    {
        $validated = $request->validate([
            'class_schedule_detail_id' => 'required|exists:class_schedule_details,id',
            'name' => 'required|string',
            'type' => 'required|in:standar_k13,merdeka,custom',
            'knowledge_formula' => 'nullable|array',
            'skill_formula' => 'nullable|array',
            'attendance_weight' => 'nullable|numeric|min:0|max:100',
        ]);

        $formula = AssessmentFormula::updateOrCreate(
            ['class_schedule_detail_id' => $validated['class_schedule_detail_id']],
            $validated
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Formula saved successfully',
            'data' => $formula
        ]);
    }

    /**
     * Get assessment formula for a class schedule detail
     */
    public function getFormula($detailId)
    {
        $formula = AssessmentFormula::where('class_schedule_detail_id', $detailId)->first();
        
        if (!$formula) {
            // Default formula Merdeka template
            $formula = [
                'class_schedule_detail_id' => $detailId,
                'name' => 'Standar Merdeka',
                'type' => 'merdeka',
                'knowledge_formula' => ['tugas' => 30, 'uts' => 30, 'uas' => 40],
                'skill_formula' => ['proyek' => 50, 'portfolio' => 50],
                'attendance_weight' => 0
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Formula retrieved',
            'data' => $formula
        ]);
    }

    /**
     * Save assessment scores for multiple students/components
     */
    public function saveScore(Request $request)
    {
        $validated = $request->validate([
            'class_schedule_detail_id' => 'required|exists:class_schedule_details,id',
            'semester' => 'required|in:1,2',
            'assessments' => 'required|array',
            'assessments.*.student_id' => 'required|exists:students,id',
            'assessments.*.tugas_score' => 'nullable|numeric|min:0|max:100',
            'assessments.*.uh_score' => 'nullable|numeric|min:0|max:100',
            'assessments.*.uts_score' => 'nullable|numeric|min:0|max:100',
            'assessments.*.uas_score' => 'nullable|numeric|min:0|max:100',
            'assessments.*.praktik_score' => 'nullable|numeric|min:0|max:100',
            'assessments.*.proyek_score' => 'nullable|numeric|min:0|max:100',
            'assessments.*.portfolio_score' => 'nullable|numeric|min:0|max:100',
            'assessments.*.attitude_spiritual' => 'nullable|in:A,B,C,D',
            'assessments.*.attitude_social' => 'nullable|in:A,B,C,D',
            'assessments.*.attitude_description' => 'nullable|string',
            'assessments.*.final_knowledge_score' => 'nullable|numeric',
            'assessments.*.final_skill_score' => 'nullable|numeric',
            'assessments.*.final_score' => 'nullable|numeric',
        ]);

        $detail = ClassScheduleDetail::with('classSchedule')->findOrFail($validated['class_schedule_detail_id']);
        $academicYearId = $detail->classSchedule->academic_year_id;
        $semester = $validated['semester'];

        DB::beginTransaction();
        try {
            foreach ($validated['assessments'] as $assessmentData) {
                // Remove student_id from update array to use it in match array
                $studentId = $assessmentData['student_id'];
                unset($assessmentData['student_id']);
                
                StudentAssessment::updateOrCreate(
                    [
                        'class_schedule_detail_id' => $detail->id,
                        'student_id' => $studentId,
                        'semester' => $semester
                    ],
                    array_merge($assessmentData, ['academic_year_id' => $academicYearId])
                );
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Scores saved successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save scores: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Generate report/recap for all students in a class schedule detail
     */
    public function report($detailId, Request $request)
    {
        $semester = $request->query('semester', '1');
        
        $assessments = StudentAssessment::with('student')
            ->where('class_schedule_detail_id', $detailId)
            ->where('semester', $semester)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Assessment report retrieved',
            'data' => $assessments
        ]);
    }
}
