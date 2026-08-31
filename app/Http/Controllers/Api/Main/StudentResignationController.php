<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentResignation;
use App\Models\Student;
use App\Http\Requests\StudentResignationRequest;
use App\Http\Resources\StudentResignationResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Exception;

class StudentResignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');
            $perPage = $request->query('per_page', 25);
            $sortBy = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');
            $status = $request->query('status');
            $type = $request->query('submission_type');

            $query = StudentResignation::with([
                'student.program',
                'student.hostel',
                'processor'
            ]);

            if ($search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($type) {
                $query->where('submission_type', $type);
            }

            $query->orderBy($sortBy, $sortOrder);
            $resignations = $query->paginate($perPage);

            $summary = [
                'pending' => StudentResignation::where('status', 'pending')->count(),
                'proses' => StudentResignation::where('status', 'proses')->count(),
                'disetujui' => StudentResignation::where('status', 'disetujui')->count(),
                'ditolak' => StudentResignation::where('status', 'ditolak')->count(),
            ];

            $data = $resignations->toArray();
            $data['summary'] = $summary;

            return new StudentResignationResource('Data pengajuan keluar berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentResignationRequest $request)
    {
        try {
            $student = Student::findOrFail($request->student_id);

            // Derive submission type from student status
            $submissionType = $request->submission_type;
            if (!$submissionType) {
                if ($student->status === 'Aktif') {
                    $submissionType = 'biasa';
                } else if ($student->status === 'Tugas') {
                    $submissionType = 'pasca_tugas';
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Status santri tidak valid untuk pengajuan keluar (harus Aktif atau Tugas).',
                    ], 422);
                }
            } else {
                if ($submissionType === 'biasa' && $student->status !== 'Aktif') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Santri biasa yang mengajukan keluar harus memiliki status aktif (belum bertugas).',
                    ], 422);
                } else if ($submissionType === 'pasca_tugas' && $student->status !== 'Tugas') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Santri pasca tugas harus memiliki status Tugas.',
                    ], 422);
                }
            }

            // Check duplicate pending/proses
            $duplicate = StudentResignation::where('student_id', $student->id)
                ->whereIn('status', ['pending', 'proses'])
                ->first();

            if ($duplicate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Santri ini sudah memiliki pengajuan keluar yang sedang diproses.',
                ], 422);
            }

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('uploads/resignations', $fileName, 'public');
            }

            $resignation = StudentResignation::create([
                'student_id' => $student->id,
                'submission_type' => $submissionType,
                'status' => 'pending',
                'attachment_path' => $filePath,
                'note' => $request->note,
            ]);

            return new StudentResignationResource('Pengajuan keluar berhasil diajukan', $resignation, 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat pengajuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $resignation = StudentResignation::with([
                'student.program',
                'student.hostel',
                'student.parents',
                'processor'
            ])->findOrFail($id);

            // If biasa, append violations history
            if ($resignation->submission_type === 'biasa') {
                $resignation->student->load([
                    'violations.violation.category',
                    'violations.sanctions.sanction',
                    'violations.reporter'
                ]);
            }

            return new StudentResignationResource('Detail pengajuan berhasil diambil', $resignation, 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentResignationRequest $request, string $id)
    {
        try {
            $resignation = StudentResignation::findOrFail($id);

            DB::beginTransaction();

            // Handle attachment replacement
            $filePath = $resignation->attachment_path;
            if ($request->hasFile('attachment')) {
                // Delete old file
                if ($resignation->attachment_path && Storage::disk('public')->exists($resignation->attachment_path)) {
                    Storage::disk('public')->delete($resignation->attachment_path);
                }

                $file = $request->file('attachment');
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('uploads/resignations', $fileName, 'public');
            }

            // Prepare update data
            $updateData = [
                'note' => $request->note,
                'attachment_path' => $filePath,
            ];

            // If status is changed
            if ($request->has('status') && $request->status !== $resignation->status) {
                $updateData['status'] = $request->status;

                // Process logs
                if (in_array($request->status, ['disetujui', 'ditolak', 'proses'])) {
                    $updateData['processed_by'] = auth()->id();
                    $updateData['processed_at'] = now();
                }

                // If approved, update student status
                if ($request->status === 'disetujui') {
                    $student = Student::findOrFail($resignation->student_id);
                    if ($resignation->submission_type === 'pasca_tugas') {
                        $student->update(['status' => 'Lulus']);
                    } else {
                        $student->update(['status' => 'Tidak Aktif']);
                    }
                }
            }

            $resignation->update($updateData);

            DB::commit();

            $resignation->load(['student.program', 'student.hostel', 'processor']);

            return new StudentResignationResource('Pengajuan keluar berhasil diperbarui', $resignation, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui pengajuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $resignation = StudentResignation::findOrFail($id);

            // Block delete if already approved/rejected for safety, or allow any
            if ($resignation->status === 'disetujui') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengajuan yang sudah disetujui tidak dapat dihapus.',
                ], 422);
            }

            // Delete attachment file
            if ($resignation->attachment_path && Storage::disk('public')->exists($resignation->attachment_path)) {
                Storage::disk('public')->delete($resignation->attachment_path);
            }

            $resignation->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan keluar berhasil dihapus',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus pengajuan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
