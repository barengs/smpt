<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Employment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class EmploymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $employments = Employment::latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data pekerjaan berhasil diambil',
                'data' => $employments
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pekerjaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $employment = Employment::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Jenis pekerjaan berhasil ditambahkan',
                'data' => $employment
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
                'message' => 'Gagal menambahkan jenis pekerjaan',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan jenis pekerjaan',
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
            $employment = Employment::find($id);

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis pekerjaan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data jenis pekerjaan berhasil diambil',
                'data' => $employment
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jenis pekerjaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $employment = Employment::find($id);

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis pekerjaan tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $employment->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Jenis pekerjaan berhasil diperbarui',
                'data' => $employment
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jenis pekerjaan',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui jenis pekerjaan',
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
            $employment = Employment::find($id);

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis pekerjaan tidak ditemukan'
                ], 404);
            }

            $employment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jenis pekerjaan berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jenis pekerjaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $employment = Employment::withTrashed()->find($id);

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis pekerjaan tidak ditemukan'
                ], 404);
            }

            if (!$employment->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis pekerjaan tidak dalam keadaan terhapus'
                ], 400);
            }

            $employment->restore();

            return response()->json([
                'success' => true,
                'message' => 'Jenis pekerjaan berhasil dipulihkan',
                'data' => $employment
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan jenis pekerjaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the trashed resource.
     */
    public function trashed()
    {
        try {
            $employments = Employment::onlyTrashed()->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data jenis pekerjaan terhapus berhasil diambil',
                'data' => $employments
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jenis pekerjaan terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
