<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\PositionAssignment;
use App\Models\Staff;
use App\Models\Position;
use App\Http\Resources\PositionAssignmentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;
use App\Imports\PositionAssignmentImport;
use App\Exports\PositionAssignmentReadableExport;
use App\Exports\PositionAssignmentBackupExport;
use App\Exports\PositionAssignmentTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class PositionAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $assignments = PositionAssignment::with(['position.organization', 'staff'])->get();
            return new PositionAssignmentResource('Data penugasan berhasil diambil', $assignments, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'position_id' => 'required|exists:positions,id',
                'staff_id' => 'required|exists:staff,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'assignment_letter' => 'nullable|string',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new PositionAssignmentResource('Validasi gagal', $validator->errors(), 422);
            }

            // Check if staff already has an active assignment for the SAME position
            if ($request->is_active) {
                $existingActiveAssignment = PositionAssignment::where('staff_id', $request->staff_id)
                    ->where('position_id', $request->position_id)
                    ->where('is_active', true)
                    ->first();

                if ($existingActiveAssignment) {
                    return new PositionAssignmentResource('Staff ini sudah memiliki penugasan aktif untuk jabatan tersebut', null, 409);
                }
            }

            DB::beginTransaction();

            $assignment = PositionAssignment::create($request->all());
            $assignment->load(['position.organization', 'staff']);

            DB::commit();

            return new PositionAssignmentResource('Penugasan berhasil ditambahkan', $assignment, 201);
        } catch (QueryException $e) {
            DB::rollBack();
            return new PositionAssignmentResource('Gagal menambahkan penugasan', $e->getMessage(), 500);
        } catch (Exception $e) {
            DB::rollBack();
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $assignment = PositionAssignment::with(['position.organization', 'staff'])->findOrFail($id);
            return new PositionAssignmentResource('Data penugasan berhasil diambil', $assignment, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Data penugasan tidak ditemukan', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $assignment = PositionAssignment::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'position_id' => 'required|exists:positions,id',
                'staff_id' => 'required|exists:staff,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'assignment_letter' => 'nullable|string',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation Failed PositionAssignment: ', $validator->errors()->toArray());
                return new PositionAssignmentResource('Validasi gagal', $validator->errors(), 422);
            }

            // Check if staff already has an active assignment for the SAME position (excluding current assignment)
            if ($request->is_active) {
                $existingActiveAssignment = PositionAssignment::where('staff_id', $request->staff_id)
                    ->where('position_id', $request->position_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingActiveAssignment) {
                    return new PositionAssignmentResource('Staff ini sudah memiliki penugasan aktif untuk jabatan tersebut', null, 409);
                }
            }

            DB::beginTransaction();

            $assignment->update($request->all());
            $assignment->load(['position.organization', 'staff']);

            DB::commit();

            return new PositionAssignmentResource('Penugasan berhasil diperbarui', $assignment, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $assignment = PositionAssignment::findOrFail($id);
            $assignment->delete();

            return new PositionAssignmentResource('Penugasan berhasil dihapus', null, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get current assignments (active only)
     */
    public function getCurrent()
    {
        try {
            $assignments = PositionAssignment::where('is_active', true)
                ->with(['position.organization', 'staff'])
                ->get();

            return new PositionAssignmentResource('Data penugasan aktif berhasil diambil', $assignments, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get assignments by staff
     */
    public function getByStaff($staffId)
    {
        try {
            $assignments = PositionAssignment::where('staff_id', $staffId)
                ->with(['position.organization', 'staff'])
                ->get();

            return new PositionAssignmentResource('Data penugasan berhasil diambil', $assignments, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get assignments by position
     */
    public function getByPosition($positionId)
    {
        try {
            $assignments = PositionAssignment::where('position_id', $positionId)
                ->with(['position.organization', 'staff'])
                ->get();

            return new PositionAssignmentResource('Data penugasan berhasil diambil', $assignments, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Export position assignments to Excel (Readable)
     */
    public function export()
    {
        try {
            return Excel::download(new PositionAssignmentReadableExport, 'laporan_kepengurusan_' . date('Y-m-d_H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        } catch (\Exception $e) {
            Log::error('Position Assignment Export error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Backup position assignments to CSV (Raw)
     */
    public function backup()
    {
        try {
            return Excel::download(new PositionAssignmentBackupExport, 'backup_kepengurusan_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
        } catch (\Exception $e) {
            Log::error('Position Assignment Backup error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Import position assignments from Excel or CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $import = new PositionAssignmentImport();
            
            Excel::import($import, $file);

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            $failureCount = $import->getFailureCount();

            $response = [
                'success' => true,
                'message' => $successCount > 0 ? 'Import completed' : 'Import failed',
                'data' => [
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                    'errors' => $errors,
                ]
            ];

            if ($failureCount > 0) {
                $response['message'] = $successCount > 0 ? 'Import completed with some errors' : 'Import failed with errors';
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('Position Assignment Import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download Excel template for position assignment import
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new PositionAssignmentTemplateExport,
                'position_assignment_import_template.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            Log::error('Position Assignment Template download error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh template: ' . $e->getMessage()
            ], 500);
        }
    }
}
