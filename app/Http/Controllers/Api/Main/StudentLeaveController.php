<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentLeaveRequest;
use App\Http\Requests\UpdateStudentLeaveRequest;
use App\Http\Requests\StoreLeaveReportRequest;
use App\Http\Resources\StudentLeaveResource;
use App\Models\StudentLeave;
use App\Models\StudentLeaveReport;
use App\Models\StudentLeavePenalty;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class StudentLeaveController extends Controller
{
    /**
     * List student leaves with filters
     *
     * Query params:
     * - student_id: integer (optional)
     * - leave_type_id: integer (optional)
     * - status: enum(pending,approved,rejected,active,completed,overdue,cancelled) (optional)
     * - academic_year_id: integer (optional)
     * - start_date: date (optional)
     * - end_date: date (optional)
     * - per_page: integer (optional, default 15)
     */
    public function index(Request $request)
    {
        try {
            $query = StudentLeave::with([
                'student:id,first_name,last_name,nis',
                'leaveType',
                'academicYear',
                'approver:id,first_name,last_name',
                'report',
                'penalties'
            ]);

            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->has('leave_type_id')) {
                $query->where('leave_type_id', $request->leave_type_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            if ($request->has('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }

            $perPage = $request->input('per_page', 15);
            $leaves = $query->orderByDesc('created_at')->paginate($perPage);

            return StudentLeaveResource::collection($leaves);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new leave request
     *
     * Body:
     * - student_id: integer (required)
     * - leave_type_id: integer (required)
     * - academic_year_id: integer (optional)
     * - start_date: date (required)
     * - end_date: date (required)
     * - reason: string (required)
     * - destination: string (optional)
     * - contact_person: string (optional)
     * - contact_phone: string (optional)
     * - notes: string (optional)
     */
    public function store(StoreStudentLeaveRequest $request)
    {
        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $durationDays = $startDate->diffInDays($endDate) + 1;

            // Get current academic year if not provided
            $academicYearId = $request->academic_year_id;
            if (!$academicYearId) {
                $currentAcademicYear = AcademicYear::where('is_active', true)->first();
                $academicYearId = $currentAcademicYear?->id;
            }

            $leave = StudentLeave::create([
                'student_id' => $request->student_id,
                'leave_type_id' => $request->leave_type_id,
                'academic_year_id' => $academicYearId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_days' => $durationDays,
                'reason' => $request->reason,
                'destination' => $request->destination,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'expected_return_date' => $endDate->copy()->addDay(),
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan izin berhasil dibuat',
                'data' => new StudentLeaveResource($leave->load([
                    'student', 'leaveType', 'academicYear'
                ]))
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pengajuan izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show leave detail
     *
     * Path:
     * - id: integer (required)
     */
    public function show(string $id)
    {
        try {
            $leave = StudentLeave::with([
                'student',
                'leaveType',
                'academicYear',
                'approver',
                'report.reportedToStaff',
                'report.verifiedByStaff',
                'penalties.sanction',
                'penalties.assignedByStaff'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new StudentLeaveResource($leave)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data izin tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update leave request (only if still pending)
     *
     * Path:
     * - id: integer (required)
     * Body:
     * - leave_type_id: integer (optional)
     * - start_date: date (optional)
     * - end_date: date (optional)
     * - reason: string (optional)
     * - destination: string (optional)
     * - contact_person: string (optional)
     * - contact_phone: string (optional)
     * - notes: string (optional)
     */
    public function update(UpdateStudentLeaveRequest $request, string $id)
    {
        try {
            $leave = StudentLeave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya izin dengan status pending yang dapat diubah'
                ], 422);
            }

            DB::beginTransaction();

            $updateData = $request->only([
                'leave_type_id', 'reason', 'destination',
                'contact_person', 'contact_phone', 'notes'
            ]);

            if ($request->has('start_date') || $request->has('end_date')) {
                $startDate = Carbon::parse($request->start_date ?? $leave->start_date);
                $endDate = Carbon::parse($request->end_date ?? $leave->end_date);

                $updateData['start_date'] = $startDate;
                $updateData['end_date'] = $endDate;
                $updateData['duration_days'] = $startDate->diffInDays($endDate) + 1;
                $updateData['expected_return_date'] = $endDate->copy()->addDay();
            }

            $leave->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Izin berhasil diperbarui',
                'data' => new StudentLeaveResource($leave->load([
                    'student', 'leaveType', 'academicYear'
                ]))
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete/cancel leave request
     *
     * Path:
     * - id: integer (required)
     */
    public function destroy(string $id)
    {
        try {
            $leave = StudentLeave::findOrFail($id);

            if (!in_array($leave->status, ['pending', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin tidak dapat dibatalkan'
                ], 422);
            }

            DB::beginTransaction();
            $leave->update(['status' => 'cancelled']);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Izin berhasil dibatalkan'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve leave request
     *
     * Path:
     * - id: integer (required)
     * Body:
     * - approval_notes: string (optional)
     */
    public function approve(Request $request, string $id)
    {
        try {
            $leave = StudentLeave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya izin dengan status pending yang dapat disetujui'
                ], 422);
            }

            DB::beginTransaction();

            $staffId = Auth::user()->staff->id ?? null;

            $leave->update([
                'status' => 'approved',
                'approved_by' => $staffId,
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Izin berhasil disetujui',
                'data' => new StudentLeaveResource($leave->load([
                    'student', 'leaveType', 'approver'
                ]))
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject leave request
     *
     * Path:
     * - id: integer (required)
     * Body:
     * - approval_notes: string (required)
     */
    public function reject(Request $request, string $id)
    {
        $request->validate([
            'approval_notes' => 'required|string|min:10'
        ], [
            'approval_notes.required' => 'Alasan penolakan harus diisi',
            'approval_notes.min' => 'Alasan penolakan minimal 10 karakter'
        ]);

        try {
            $leave = StudentLeave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya izin dengan status pending yang dapat ditolak'
                ], 422);
            }

            DB::beginTransaction();

            $staffId = Auth::user()->staff->id ?? null;

            $leave->update([
                'status' => 'rejected',
                'approved_by' => $staffId,
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Izin ditolak',
                'data' => new StudentLeaveResource($leave->load([
                    'student', 'leaveType', 'approver'
                ]))
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit leave return report
     *
     * Path:
     * - id: integer (required) - student leave ID
     * Body:
     * - report_date: date (required)
     * - report_time: time H:i (optional)
     * - report_notes: string (optional)
     * - condition: enum(sehat,sakit,lainnya) (required)
     * - reported_to: integer (optional) - staff ID
     */
    public function submitReport(StoreLeaveReportRequest $request, string $id)
    {
        try {
            $leave = StudentLeave::findOrFail($id);

            if (!$leave->canBeReported()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin tidak dapat dilaporkan. Status harus approved, active, atau overdue'
                ], 422);
            }

            if ($leave->report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan sudah pernah dibuat untuk izin ini'
                ], 422);
            }

            DB::beginTransaction();

            $reportDate = Carbon::parse($request->report_date);
            $expectedReturnDate = Carbon::parse($leave->expected_return_date);

            $isLate = $reportDate->isAfter($expectedReturnDate);
            $lateDays = $isLate ? $reportDate->diffInDays($expectedReturnDate) : 0;

            $report = StudentLeaveReport::create([
                'student_leave_id' => $leave->id,
                'report_date' => $reportDate,
                'report_time' => $request->report_time ?? now()->format('H:i'),
                'report_notes' => $request->report_notes,
                'condition' => $request->condition,
                'is_late' => $isLate,
                'late_days' => $lateDays,
                'reported_to' => $request->reported_to,
            ]);

            // Update leave status
            $leave->update([
                'status' => $isLate ? 'overdue' : 'completed',
                'actual_return_date' => $reportDate,
                'has_penalty' => $isLate,
            ]);

            // Auto create penalty if late
            if ($isLate) {
                $penaltyDescription = "Terlambat lapor kembali setelah izin selama {$lateDays} hari";

                StudentLeavePenalty::create([
                    'student_leave_id' => $leave->id,
                    'student_leave_report_id' => $report->id,
                    'penalty_type' => 'peringatan',
                    'description' => $penaltyDescription,
                    'point_value' => $lateDays * 5, // 5 point per day late
                    'assigned_by' => $request->reported_to,
                    'assigned_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laporan kembali berhasil dibuat',
                'data' => new StudentLeaveResource($leave->load([
                    'student', 'leaveType', 'report', 'penalties'
                ])),
                'is_late' => $isLate,
                'late_days' => $lateDays
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign penalty to a leave
     *
     * Path:
     * - id: integer (required) - student leave ID
     * Body:
     * - penalty_type: enum(peringatan,sanksi,poin) (required)
     * - description: string (required)
     * - point_value: integer (optional)
     * - sanction_id: integer (optional)
     */
    public function assignPenalty(Request $request, string $id)
    {
        $request->validate([
            'penalty_type' => 'required|in:peringatan,sanksi,poin',
            'description' => 'required|string',
            'point_value' => 'nullable|integer|min:0',
            'sanction_id' => 'nullable|exists:sanctions,id',
        ]);

        try {
            $leave = StudentLeave::findOrFail($id);

            DB::beginTransaction();

            $staffId = Auth::user()->staff->id ?? null;

            $penalty = StudentLeavePenalty::create([
                'student_leave_id' => $leave->id,
                'student_leave_report_id' => $leave->report?->id,
                'penalty_type' => $request->penalty_type,
                'description' => $request->description,
                'point_value' => $request->point_value ?? 0,
                'sanction_id' => $request->sanction_id,
                'assigned_by' => $staffId,
                'assigned_at' => now(),
            ]);

            $leave->update(['has_penalty' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penalti berhasil diberikan',
                'data' => $penalty->load(['sanction', 'assignedByStaff'])
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memberikan penalti',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leave statistics and summary report
     *
     * Query params:
     * - academic_year_id: integer (optional)
     * - student_id: integer (optional)
     * - start_date: date (optional)
     * - end_date: date (optional)
     */
    public function statistics(Request $request)
    {
        try {
            $query = StudentLeave::query();

            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->has('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }

            // Status statistics
            $statusStats = (clone $query)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
                ->pluck('total', 'status');

            // Leave type statistics
            $leaveTypeStats = (clone $query)
                ->join('leave_types', 'leave_types.id', '=', 'student_leaves.leave_type_id')
                ->select('leave_types.name', DB::raw('count(*) as total'))
                ->groupBy('leave_types.id', 'leave_types.name')
                ->get();

            // Overdue/Late statistics
            $overdueCount = (clone $query)->where('status', 'overdue')->count();
            $withPenaltyCount = (clone $query)->where('has_penalty', true)->count();

            // Monthly trend
            $monthlyTrend = (clone $query)
                ->select(
                    DB::raw('YEAR(start_date) as year'),
                    DB::raw('MONTH(start_date) as month'),
                    DB::raw('count(*) as total')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();

            // Top students with most leaves
            $topStudents = (clone $query)
                ->join('students', 'students.id', '=', 'student_leaves.student_id')
                ->select(
                    'students.id',
                    'students.first_name',
                    'students.last_name',
                    'students.nis',
                    DB::raw('count(*) as total_leaves')
                )
                ->groupBy('students.id', 'students.first_name', 'students.last_name', 'students.nis')
                ->orderByDesc('total_leaves')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_leaves' => $query->count(),
                    'status_breakdown' => $statusStats,
                    'leave_type_breakdown' => $leaveTypeStats,
                    'overdue_count' => $overdueCount,
                    'with_penalty_count' => $withPenaltyCount,
                    'monthly_trend' => $monthlyTrend,
                    'top_students' => $topStudents,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed leave report for a student
     *
     * Path:
     * - studentId: integer (required)
     * Query:
     * - academic_year_id: integer (optional)
     */
    public function reportByStudent(string $studentId, Request $request)
    {
        try {
            $query = StudentLeave::with([
                'leaveType',
                'academicYear',
                'approver',
                'report',
                'penalties'
            ])->where('student_id', $studentId);

            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            $leaves = $query->orderByDesc('start_date')->get();

            $summary = [
                'total_leaves' => $leaves->count(),
                'approved_leaves' => $leaves->where('status', 'approved')->count(),
                'rejected_leaves' => $leaves->where('status', 'rejected')->count(),
                'completed_leaves' => $leaves->where('status', 'completed')->count(),
                'overdue_leaves' => $leaves->where('status', 'overdue')->count(),
                'total_days_on_leave' => $leaves->sum('duration_days'),
                'total_penalties' => $leaves->where('has_penalty', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'leaves' => StudentLeaveResource::collection($leaves)
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check and update overdue leaves (cron job endpoint)
     */
    public function checkOverdueLeaves()
    {
        try {
            DB::beginTransaction();

            $overdueLeaves = StudentLeave::where('status', 'active')
                ->whereDate('expected_return_date', '<', now())
                ->get();

            $updated = 0;
            foreach ($overdueLeaves as $leave) {
                $leave->update(['status' => 'overdue']);
                $updated++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui {$updated} izin menjadi overdue"
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa izin overdue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
