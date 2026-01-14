<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\StudentViolation;
use App\Models\StudentSanction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentViolationsReportExport;
use Exception;

class StudentViolationController extends Controller
{
    /**
     * List student violations
     *
     * Query params:
     * - student_id: integer (optional)
     * - status: enum(pending,verified,processed,cancelled) (optional)
     * - academic_year_id: integer (optional)
     * - date_from: date (optional)
     * - date_to: date (optional)
     */
    public function index(Request $request)
    {
        try {
            $query = StudentViolation::with([
                'student:id,first_name,last_name,nis',
                'violation.category',
                'academicYear',
                'reporter:id,first_name,last_name',
                'sanctions.sanction'
            ]);

            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            if ($request->has('date_from')) {
                $query->where('violation_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('violation_date', '<=', $request->date_to);
            }

            $violations = $query->orderByDesc('violation_date')->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggaran siswa berhasil diambil',
                'data' => $violations
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pelanggaran siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record a student violation
     *
     * Body:
     * - student_id: integer (required)
     * - violation_id: integer (required)
     * - academic_year_id: integer (optional)
     * - violation_date: date (required)
     * - violation_time: time H:i (optional)
     * - location: string (optional)
     * - description: string (optional)
     * - reported_by: integer (optional)
     * - notes: string (optional)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'violation_id' => 'required|exists:violations,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'violation_date' => 'required|date',
            'violation_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'reported_by' => 'nullable|exists:staff,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $violation = StudentViolation::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran siswa berhasil dicatat',
                'data' => $violation->load(['student', 'violation.category', 'reporter'])
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat pelanggaran siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a student violation by ID
     *
     * Path:
     * - id: integer (required)
     */
    public function show(string $id)
    {
        try {
            $violation = StudentViolation::with([
                'student:id,first_name,last_name,nis,program_id',
                'student.program',
                'violation.category',
                'academicYear',
                'reporter:id,first_name,last_name',
                'sanctions.sanction',
                'sanctions.assignedBy'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggaran siswa berhasil diambil',
                'data' => $violation
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggaran siswa tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update a student violation
     *
     * Path:
     * - id: integer (required)
     * Body:
     * - student_id: integer (required)
     * - violation_id: integer (required)
     * - academic_year_id: integer (optional)
     * - violation_date: date (required)
     * - violation_time: time H:i (optional)
     * - location: string (optional)
     * - description: string (optional)
     * - reported_by: integer (optional)
     * - status: enum(pending,verified,processed,cancelled) (required)
     * - notes: string (optional)
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'violation_id' => 'required|exists:violations,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'violation_date' => 'required|date',
            'violation_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'reported_by' => 'nullable|exists:staff,id',
            'status' => 'required|in:pending,verified,processed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $violation = StudentViolation::findOrFail($id);
            $violation->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran siswa berhasil diperbarui',
                'data' => $violation->load(['student', 'violation.category', 'reporter'])
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pelanggaran siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a student violation by ID
     *
     * Path:
     * - id: integer (required)
     */
    public function destroy(string $id)
    {
        try {
            $violation = StudentViolation::findOrFail($id);
            $violation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran siswa berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pelanggaran siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a sanction to a student violation
     *
     * Path:
     * - id: integer - student violation ID (required)
     * Body:
     * - sanction_id: integer (required)
     * - start_date: date (required)
     * - end_date: date (optional)
     * - notes: string (optional)
     */
    public function assignSanction(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'sanction_id' => 'required|exists:sanctions,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $violation = StudentViolation::findOrFail($id);

            DB::beginTransaction();

            $sanction = StudentSanction::create([
                'student_violation_id' => $violation->id,
                'sanction_id' => $request->sanction_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'active',
                'notes' => $request->notes,
                'assigned_by' => Auth::user()->staff->id ?? null,
            ]);

            // Update status pelanggaran jadi processed
            $violation->update(['status' => 'processed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sanksi berhasil diberikan',
                'data' => $sanction->load(['sanction', 'assignedBy'])
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memberikan sanksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get violation report for a student
     *
     * Path:
     * - studentId: integer (required)
     * Query:
     * - academic_year_id: integer (optional)
     */
    public function reportByStudent(Request $request, string $studentId)
    {
        try {
            $query = StudentViolation::with([
                'violation.category',
                'academicYear',
                'sanctions.sanction'
            ])->where('student_id', $studentId);

            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            $violations = $query->orderByDesc('violation_date')->get();

            $totalPoints = $violations->sum(function ($v) {
                return $v->violation->point ?? 0;
            });

            return response()->json([
                'success' => true,
                'message' => 'Laporan pelanggaran siswa berhasil diambil',
                'data' => [
                    'violations' => $violations,
                    'summary' => [
                        'total_violations' => $violations->count(),
                        'total_points' => $totalPoints,
                        'pending' => $violations->where('status', 'pending')->count(),
                        'processed' => $violations->where('status', 'processed')->count(),
                    ]
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan pelanggaran siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get violation statistics
     *
     * Query:
     * - academic_year_id: integer (optional)
     * - date_from: date (optional)
     * - date_to: date (optional)
     */
    public function statistics(Request $request)
    {
        try {
            $query = StudentViolation::with('violation.category');

            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            if ($request->has('date_from')) {
                $query->where('violation_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('violation_date', '<=', $request->date_to);
            }

            $violations = $query->get();

            // Statistik per kategori
            $byCategory = DB::table('student_violations as sv')
                ->join('violations as v', 'v.id', '=', 'sv.violation_id')
                ->join('violation_categories as vc', 'vc.id', '=', 'v.category_id')
                ->select('vc.name as category', DB::raw('count(*) as total'))
                ->when($request->academic_year_id, function ($q) use ($request) {
                    return $q->where('sv.academic_year_id', $request->academic_year_id);
                })
                ->groupBy('vc.id', 'vc.name')
                ->get();

            // Top pelanggar
            $topViolators = DB::table('student_violations as sv')
                ->join('students as s', 's.id', '=', 'sv.student_id')
                ->select(
                    's.id',
                    's.first_name',
                    's.last_name',
                    's.nis',
                    DB::raw('count(*) as total_violations')
                )
                ->when($request->academic_year_id, function ($q) use ($request) {
                    return $q->where('sv.academic_year_id', $request->academic_year_id);
                })
                ->groupBy('s.id', 's.first_name', 's.last_name', 's.nis')
                ->orderByDesc('total_violations')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistik pelanggaran berhasil diambil',
                'data' => [
                    'total_violations' => $violations->count(),
                    'by_status' => [
                        'pending' => $violations->where('status', 'pending')->count(),
                        'verified' => $violations->where('status', 'verified')->count(),
                        'processed' => $violations->where('status', 'processed')->count(),
                        'cancelled' => $violations->where('status', 'cancelled')->count(),
                    ],
                    'by_category' => $byCategory,
                    'top_violators' => $topViolators
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download violation report
     *
     * Query:
     * - period: enum(daily, weekly, monthly, custom) (optional, default: monthly)
     * - date_from: date (required if period=custom)
     * - date_to: date (required if period=custom)
     * - student_id: integer (optional)
     * - status: enum(pending, verified, processed, cancelled) (optional)
     * - academic_year_id: integer (optional)
     */
    public function downloadReport(Request $request)
    {
        try {
            $period = $request->input('period', 'monthly');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            // Filters
            $filters = [
                'student_id' => $request->student_id,
                'status' => $request->status,
                'academic_year_id' => $request->academic_year_id,
            ];

            return Excel::download(
                new StudentViolationsReportExport($filters, $period, $dateFrom, $dateTo),
                'laporan_pelanggaran_' . time() . '.xlsx'
            );
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

