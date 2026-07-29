<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Http\Resources\PositionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Exception;
use App\Imports\PositionImport;
use App\Exports\PositionReadableExport;
use App\Exports\PositionBackupExport;
use App\Exports\PositionTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $positions = Position::with(['organization', 'parent'])->get();
            return new PositionResource('Data jabatan berhasil diambil', $positions, 200);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:positions,code,NULL,id,deleted_at,NULL',
                'description' => 'nullable|string',
                'organization_id' => 'required|exists:organizations,id',
                'parent_id' => 'nullable|exists:positions,id',
                'level' => 'required|integer|min:1',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new PositionResource('Validasi gagal', $validator->errors(), 422);
            }

            $position = Position::create($request->all());
            $position->load(['organization', 'parent']);

            return new PositionResource('Jabatan berhasil ditambahkan', $position, 201);
        } catch (QueryException $e) {
            return new PositionResource('Gagal menambahkan jabatan', $e->getMessage(), 500);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $position = Position::with(['organization', 'parent', 'children', 'assignments.official'])->findOrFail($id);
            return new PositionResource('Data jabatan berhasil diambil', $position, 200);
        } catch (Exception $e) {
            return new PositionResource('Data jabatan tidak ditemukan', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $position = Position::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:positions,code,' . $id . ',id,deleted_at,NULL',
                'description' => 'nullable|string',
                'organization_id' => 'required|exists:organizations,id',
                'parent_id' => 'nullable|exists:positions,id',
                'level' => 'required|integer|min:1',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new PositionResource('Validasi gagal', $validator->errors(), 422);
            }

            $position->update($request->all());
            $position->load(['organization', 'parent']);

            return new PositionResource('Jabatan berhasil diperbarui', $position, 200);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $position = Position::findOrFail($id);
            $position->delete();

            return new PositionResource('Jabatan berhasil dihapus', null, 200);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get positions by organization
     */
    public function getByOrganization($organizationId)
    {
        try {
            $positions = Position::where('organization_id', $organizationId)
                ->with(['organization', 'parent'])
                ->get();

            return new PositionResource('Data jabatan berhasil diambil', $positions, 200);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Export positions to Excel (Readable)
     */
    public function export()
    {
        try {
            return Excel::download(new PositionReadableExport, 'laporan_jabatan_' . date('Y-m-d_H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        } catch (\Exception $e) {
            Log::error('Position Export error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Backup positions to CSV (Raw)
     */
    public function backup()
    {
        try {
            return Excel::download(new PositionBackupExport, 'backup_jabatan_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
        } catch (\Exception $e) {
            Log::error('Position Backup error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Import positions from Excel or CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $import = new PositionImport();
            
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
            Log::error('Position Import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download Excel template for position import
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new PositionTemplateExport,
                'position_import_template.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            Log::error('Position Template download error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh template: ' . $e->getMessage()
            ], 500);
        }
    }
}
