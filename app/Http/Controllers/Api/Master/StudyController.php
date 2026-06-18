<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudyImport;
use App\Exports\StudyTemplateExport;

class StudyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $studies = Study::all();
            return response()->json([
                'success' => true,
                'data' => $studies
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data studi: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string'
            ]);

            $study = Study::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Studi berhasil dibuat',
                'data' => $study
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan studi: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $study = Study::find($id);

            if (!$study) {
                return response()->json([
                    'success' => false,
                    'message' => 'Studi tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $study
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data studi: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $study = Study::find($id);

            if (!$study) {
                return response()->json([
                    'success' => false,
                    'message' => 'Studi tidak ditemukan'
                ], 404);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string'
            ]);

            $study->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Studi berhasil diperbarui',
                'data' => $study
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui studi: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $study = Study::find($id);

            if (!$study) {
                return response()->json([
                    'success' => false,
                    'message' => 'Studi tidak ditemukan'
                ], 404);
            }

            $study->delete();

            return response()->json([
                'success' => true,
                'message' => 'Studi berhasil dihapus'
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus studi: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            // Mencari studi yang telah dihapus
            $study = Study::withTrashed()->find($id);

            if (!$study) {
                return response()->json([
                    'success' => false,
                    'message' => 'Studi tidak ditemukan'
                ], 404);
            }

            // Memastikan studi dalam keadaan terhapus
            if (!$study->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Studi tidak dalam keadaan terhapus'
                ], 400);
            }

            // Memulihkan studi
            $study->restore();

            return response()->json([
                'success' => true,
                'message' => 'Studi berhasil dipulihkan',
                'data' => $study
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan studi: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the trashed resource.
     */
    public function trashed()
    {
        try {
            $trashedStudies = Study::onlyTrashed()->get();

            return response()->json([
                'success' => true,
                'data' => $trashedStudies
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data studi yang terhapus: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export study data to Excel (Readable)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StudyReadableExport, 'laporan_studi_' . date('Y-m-d_H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Backup study data to CSV (Raw)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function backup()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StudyBackupExport, 'backup_studi_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Import study from Excel or CSV file
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            ]);

            $file = $request->file('file');
            $import = new StudyImport();

            Excel::import($import, $file);

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            $failureCount = $import->getFailureCount();

            $response = [
                'success' => true,
                'message' => 'Import completed',
                'data' => [
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                    'total' => $successCount + $failureCount,
                ]
            ];

            if (count($errors) > 0) {
                $response['data']['errors'] = array_slice($errors, 0, 50);
                $response['data']['total_errors'] = count($errors);
                $response['message'] = 'Import completed with some errors';
            }

            return response()->json($response, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel template for study import
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new StudyTemplateExport(),
                'study_import_template.xlsx'
            );
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh template',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
