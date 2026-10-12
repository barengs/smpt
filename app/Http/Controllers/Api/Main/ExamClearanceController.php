<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\AcademicQuarter;
use App\Models\Student;
use App\Models\StudentExamDispensation;
use App\Services\ExamClearanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Exam Clearance Controller (Gerbang Ujian Triwulan)
 *
 * Kebijakan finansial-akademik berada di SMPT (domain pendidikan),
 * data tunggakan diambil real-time dari bank-santri via X-Internal-Key.
 */
class ExamClearanceController extends Controller
{
    protected ExamClearanceService $clearance;

    public function __construct(ExamClearanceService $clearance)
    {
        $this->clearance = $clearance;
    }

    /**
     * Cek kelayakan ujian satu santri.
     * GET /api/main/exam-clearance/check/{studentId}
     */
    public function check($studentId)
    {
        $student = Student::findOrFail($studentId);
        $result  = $this->clearance->check($student);

        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }

    /**
     * Daftar kelayakan seluruh santri peserta triwulan (untuk panitia ujian).
     * GET /api/main/exam-clearance/quarter/{quarterId}/students
     */
    public function quarterStudents($quarterId, Request $request)
    {
        $quarter = AcademicQuarter::findOrFail($quarterId);

        $students = Student::query()
            ->when($request->class_id, fn($q, $cid) => $q->whereHas('activeRoom', fn($r) => $r->where('classrooms.id', $cid)))
            ->orderBy('first_name')
            ->get();

        $result = $this->clearance->checkBatch($students, $quarter);

        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }

    /**
     * Berikan dispensasi ujian (komitmen bayar wali) kepada santri menunggak.
     * POST /api/main/exam-clearance/dispensation
     */
    public function grantDispensation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id'          => 'required|exists:students,id',
            'academic_quarter_id' => 'nullable|exists:academic_quarters,id',
            'commitment_date'     => 'required|date|after_or_equal:today',
            'committed_amount'    => 'required|numeric|min:0',
            'reason'              => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $dispensation = StudentExamDispensation::updateOrCreate(
            [
                'student_id'          => $request->student_id,
                'academic_quarter_id' => $request->academic_quarter_id,
                'status'              => 'active',
            ],
            [
                'commitment_date'  => $request->commitment_date,
                'committed_amount' => $request->committed_amount,
                'reason'           => $request->reason,
                'granted_by'       => auth('api')->id(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Dispensasi ujian diberikan. Santri diizinkan mengikuti ujian sampai tanggal komitmen pembayaran.',
            'data'    => $dispensation,
        ], 201);
    }

    /**
     * Cabut / tandai lunas dispensasi.
     * PATCH /api/main/exam-clearance/dispensation/{id}
     */
    public function revokeDispensation($id, Request $request)
    {
        $dispensation = StudentExamDispensation::findOrFail($id);
        $status = $request->get('status', 'revoked');

        if (!in_array($status, ['revoked', 'fulfilled'])) {
            return response()->json(['status' => 'error', 'message' => 'Status harus revoked atau fulfilled.'], 422);
        }

        $dispensation->update([
            'status'     => $status,
            'revoked_by' => auth('api')->id(),
            'revoked_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => $status === 'fulfilled'
                ? 'Dispensasi ditandai lunas — komitmen pembayaran terpenuhi.'
                : 'Dispensasi dicabut — santri kembali dinilai berdasarkan tunggakan.',
            'data'    => $dispensation,
        ]);
    }

    /**
     * Daftar dispensasi.
     * GET /api/main/exam-clearance/dispensations
     */
    public function dispensations(Request $request)
    {
        $dispensations = StudentExamDispensation::with(['student', 'academicQuarter', 'grantedBy'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->quarter_id, fn($q, $qid) => $q->where('academic_quarter_id', $qid))
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $dispensations,
        ]);
    }
}
