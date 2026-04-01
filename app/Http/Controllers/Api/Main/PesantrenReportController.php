<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Hostel;
use App\Models\StudentViolation;
use App\Models\StudentLeave;
use App\Models\Presence;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PesantrenReportController extends Controller
{
    /**
     * Get Student Statistics
     */
    public function studentStatistics(Request $request)
    {
        try {
            $activeYear = AcademicYear::where('active', 1)->first();
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $startOfYear = $now->copy()->startOfYear();

            // 1. Basic Stats
            $totalActive = Student::where('status', 'aktif')->count();
            $totalNew = Student::whereBetween('created_at', [$startOfYear, $now])->count();
            $totalGraduated = Student::where('status', 'tamat')->count();

            // 2. Stats by Hostel
            $byHostel = Student::select('hostel_id', DB::raw('count(*) as count'))
                ->with('hostel:id,name')
                ->where('status', 'aktif')
                ->groupBy('hostel_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'hostel_name' => $item->hostel->name ?? 'Tanpa Asrama',
                        'count' => $item->count
                    ];
                });

            // 3. Stats by Gender
            $byGender = Student::select('gender', DB::raw('count(*) as count'))
                ->where('status', 'aktif')
                ->groupBy('gender')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'summary' => [
                        'active' => $totalActive,
                        'new' => $totalNew,
                        'graduated' => $totalGraduated,
                    ],
                    'by_hostel' => $byHostel,
                    'by_gender' => $byGender,
                    'academic_year' => $activeYear ? $activeYear->year : 'N/A'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik santri: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Violation Report
     */
    public function violationReport(Request $request)
    {
        try {
            $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
            $endDate = $request->end_date ?? Carbon::now()->toDateString();

            $violations = StudentViolation::with(['student:id,first_name,last_name,nis,hostel_id', 'student.hostel:id,name', 'violation:id,name,category_id', 'violation.category:id,name,severity_level'])
                ->whereBetween('violation_date', [$startDate, $endDate])
                ->orderByDesc('violation_date')
                ->get();

            $statsByCategory = StudentViolation::join('violations', 'student_violations.violation_id', '=', 'violations.id')
                ->join('violation_categories', 'violations.category_id', '=', 'violation_categories.id')
                ->whereBetween('violation_date', [$startDate, $endDate])
                ->select('violation_categories.name', DB::raw('count(*) as count'))
                ->groupBy('violation_categories.name')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'list' => $violations,
                    'stats' => $statsByCategory,
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil laporan pelanggaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Leave/Permit Report
     */
    public function leaveReport(Request $request)
    {
        try {
            $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
            $endDate = $request->end_date ?? Carbon::now()->toDateString();

            $leaves = StudentLeave::with(['student:id,first_name,last_name,nis', 'leaveType:id,name'])
                ->whereBetween('start_date', [$startDate, $endDate])
                ->orderByDesc('start_date')
                ->get();

            $statsByStatus = StudentLeave::whereBetween('start_date', [$startDate, $endDate])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'list' => $leaves,
                    'stats' => $statsByStatus,
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil laporan perizinan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Attendance Statistics
     */
    public function attendanceStatistics(Request $request)
    {
        try {
            $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
            $endDate = $request->end_date ?? Carbon::now()->toDateString();

            // Group by Lesson/Class via MeetingSchedule
            $stats = Presence::join('meeting_schedules', 'presences.meeting_schedule_id', '=', 'meeting_schedules.id')
                ->join('class_schedule_details', 'meeting_schedules.class_schedule_detail_id', '=', 'class_schedule_details.id')
                ->join('studies', 'class_schedule_details.study_id', '=', 'studies.id')
                ->whereBetween('presences.date', [$startDate, $endDate])
                ->select(
                    'studies.name as study_name',
                    'presences.status',
                    DB::raw('count(*) as count')
                )
                ->groupBy('studies.name', 'presences.status')
                ->get();

            // Transform into a more readable format
            $formattedStats = $stats->groupBy('study_name')->map(function ($items, $study) {
                $statusCounts = $items->pluck('count', 'status');
                $total = $statusCounts->sum();
                return [
                    'study_name' => $study,
                    'present' => $statusCounts->get('hadir', 0),
                    'absent' => $statusCounts->get('alfa', 0),
                    'sick' => $statusCounts->get('sakit', 0),
                    'permit' => $statusCounts->get('izin', 0),
                    'total' => $total,
                    'percentage' => $total > 0 ? round(($statusCounts->get('hadir', 0) / $total) * 100, 2) : 0
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'attendance' => $formattedStats,
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik presensi: ' . $e->getMessage()
            ], 500);
        }
    }
}
