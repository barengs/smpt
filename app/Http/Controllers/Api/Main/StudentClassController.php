<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Classroom;
use App\Models\Education;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StudentClassRequest;
use App\Http\Resources\StudentClassResource;

class StudentClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = StudentClass::with(['academicYears', 'educations', 'students', 'classrooms', 'classGroup']);

            // Filter by academic year if provided
            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            // Filter by education if provided
            if ($request->has('educational_institution_id')) {
                $query->where('educational_institution_id', $request->educational_institution_id);
            }

            // Filter by approval status if provided
            if ($request->has('approval_status')) {
                $query->where('approval_status', $request->approval_status);
            }

            $studentClasses = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'message' => 'Data kelas siswa berhasil diambil',
                'status' => 200,
                'data' => $studentClasses
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching student classes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data kelas siswa',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentClassRequest $request)
    {
        $request->validated([
            'academic_year_id' => 'required|exists:academic_years,id',
            'educational_institution_id' => 'required|exists:educational_institutions,id',
            'class_id' => 'required|exists:classrooms,id',
            'class_group_id' => 'required|exists:class_groups,id',
            'student_id' => 'required|exists:students,id',
            'approval_status' => 'required|in:diajukan,disetujui,ditolak'
        ]);

        try {
            $checkStudent = StudentClass::where('student_id', $request->student_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('educational_institution_id', $request->educational_institution_id)
                ->where('class_id', $request->class_id)
                ->where('class_group_id', $request->class_group_id)
                ->where('approval_status', 'diajukan')
                ->first();

            if ($checkStudent) {
                return response()->json([
                    'message' => 'Siswa sudah terdaftar dalam kelas pada tahun akademik dan pendidikan yang sama',
                    'status' => 400
                ], 400);
            }

            DB::beginTransaction();
            $studentClass = StudentClass::create($request->validated());
            DB::commit();
            return new StudentClassResource('Data kelas siswa berhasil ditambahkan', $studentClass->load(['academicYears', 'students', 'classrooms']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating student class: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan data kelas siswa',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $studentClass = StudentClass::with(['academicYears', 'educations', 'students', 'classrooms', 'classGroup'])
                ->findOrFail($id);

            return new StudentClassResource('Data kelas siswa berhasil diambil', $studentClass, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching student class: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data kelas siswa',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentClassRequest $request, string $id)
    {
        try {
            $studentClass = StudentClass::findOrFail($id);

            $studentClass->update($request->validated());

            return new StudentClassResource('Data kelas siswa berhasil diperbarui', $studentClass->fresh()->load(['academicYears', 'students', 'classrooms', 'classGroup']), 200);
        } catch (\Exception $e) {
            Log::error('Error updating student class: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data kelas siswa',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $studentClass = StudentClass::findOrFail($id);
            $studentClass->delete();

            return response()->json([
                'message' => 'Data kelas siswa berhasil dihapus',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting student class: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data kelas siswa',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign students to classes for promotion
     */
    public function bulkAssign(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'education_id' => 'required|exists:educations,id',
                'class_id' => 'required|exists:classrooms,id',
                'student_ids' => 'required|array',
                'student_ids.*' => 'exists:students,id'
            ]);

            $assignments = [];
            foreach ($validatedData['student_ids'] as $studentId) {
                $assignments[] = [
                    'academic_year_id' => $validatedData['academic_year_id'],
                    'education_id' => $validatedData['education_id'],
                    'student_id' => $studentId,
                    'class_id' => $validatedData['class_id'],
                    'approval_status' => 'diajukan',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            DB::table('student_classes')->upsert(
                $assignments,
                ['student_id', 'academic_year_id'], // unique columns
                ['class_id', 'education_id', 'approval_status', 'updated_at'] // columns to update
            );

            return response()->json([
                'message' => 'Data kenaikan kelas siswa berhasil diproses',
                'status' => 200,
                'data' => $assignments
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error bulk assigning student classes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data kenaikan kelas siswa',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve student class promotion
     */
    public function approve(Request $request, string $id)
    {
        try {
            $studentClass = StudentClass::findOrFail($id);

            $validatedData = $request->validate([
                'approval_note' => 'nullable|string',
            ]);

            $studentClass->update([
                'approval_status' => 'disetujui',
                'approval_note' => $validatedData['approval_note'] ?? null,
                'approved_by' => Auth::id(),
            ]);

            return new StudentClassResource('Kenaikan kelas siswa berhasil disetujui', $studentClass->fresh()->load(['academicYears', 'students', 'classrooms']), 200);
        } catch (\Exception $e) {
            Log::error('Error approving student class: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyetujui kenaikan kelas siswa',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject student class promotion
     */
    public function reject(Request $request, string $id)
    {
        try {
            $studentClass = StudentClass::findOrFail($id);

            $validatedData = $request->validate([
                'approval_note' => 'required|string',
            ]);

            $studentClass->update([
                'approval_status' => 'ditolak',
                'approval_note' => $validatedData['approval_note'],
                'approved_by' => Auth::id()
            ]);

            return new StudentClassResource('Kenaikan kelas siswa berhasil ditolak', $studentClass->fresh()->load(['academicYears', 'students', 'classrooms']), 200);
        } catch (\Exception $e) {
            Log::error('Error rejecting student class: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menolak kenaikan kelas siswa',
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
