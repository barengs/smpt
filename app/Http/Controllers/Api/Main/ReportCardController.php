<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\ClassGroup;
use App\Models\StudentClass;
use App\Models\StudentAssessment;
use App\Models\AcademicYear;

class ReportCardController extends Controller
{
    /**
     * Get a list of students in a class for raport selection
     */
    public function classStudents($classGroupId, Request $request)
    {
        $academicYearId = $request->query('academic_year_id');
        $semester = $request->query('semester', '1');

        if (!$academicYearId) {
            $ay = AcademicYear::where('active', true)->first();
            $academicYearId = $ay ? $ay->id : null;
        }

        $classGroup = ClassGroup::with(['advisor', 'classroom'])->findOrFail($classGroupId);

        $studentClasses = StudentClass::with('students')
            ->where('class_group_id', $classGroupId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        $students = $studentClasses->map(function ($sc) {
            return $sc->students;
        })->filter();

        return response()->json([
            'status' => 'success',
            'message' => 'Data students for report card',
            'data' => [
                'class_group' => $classGroup,
                'students' => $students->values(),
                'raw_student_classes' => $studentClasses,
                'academic_year_id' => $academicYearId,
                'semester' => $semester
            ]
        ]);
    }

    /**
     * Get full report card details for a student
     */
    public function studentReport($classGroupId, $studentId, Request $request)
    {
        $academicYearId = $request->query('academic_year_id');
        $semester = $request->query('semester', '1');

        if (!$academicYearId) {
            $ay = AcademicYear::where('active', true)->first();
            $academicYearId = $ay ? $ay->id : null;
        } else {
            $ay = AcademicYear::find($academicYearId);
        }

        $student = Student::findOrFail($studentId);
        $classGroup = ClassGroup::with(['advisor', 'classroom'])->findOrFail($classGroupId);

        // Get assessments for the student in this semester and academic year
        $assessments = StudentAssessment::with([
            'classScheduleDetail.study', 
            'classScheduleDetail.teacher',
            'assessmentScores'
        ])
        ->where('student_id', $studentId)
        ->where('semester', $semester)
        ->whereHas('classScheduleDetail.classSchedule', function($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId);
        })
        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Student report card data retrieved',
            'data' => [
                'student' => $student,
                'class_group' => $classGroup,
                'academic_year' => $ay,
                'semester' => $semester,
                'curriculum' => 'Merdeka', // Placeholder, can be dynamic if stored in DB
                'assessments' => $assessments
            ]
        ]);
    }
}
