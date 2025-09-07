<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class EducationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $education = Education::latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data pendidikan berhasil diambil',
                'data' => $education
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pendidikan',
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
                'name' => 'required|string|max:255',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $education = Education::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pendidikan berhasil ditambahkan',
                'data' => $education
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
                'message' => 'Gagal menambahkan pendidikan',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan pendidikan',
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
            $education = Education::find($id);

            if (!$education) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendidikan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pendidikan berhasil diambil',
                'data' => $education
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pendidikan',
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
            $education = Education::find($id);

            if (!$education) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendidikan tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $education->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pendidikan berhasil diperbarui',
                'data' => $education
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
                'message' => 'Gagal memperbarui pendidikan',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui pendidikan',
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
            $education = Education::find($id);

            if (!$education) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendidikan tidak ditemukan'
                ], 404);
            }

            $education->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pendidikan berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pendidikan',
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
            $education = Education::withTrashed()->find($id);

            if (!$education) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendidikan tidak ditemukan'
                ], 404);
            }

            if (!$education->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendidikan tidak dalam keadaan terhapus'
                ], 400);
            }

            $education->restore();

            return response()->json([
                'success' => true,
                'message' => 'Pendidikan berhasil dipulihkan',
                'data' => $education
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan pendidikan',
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
            $education = Education::onlyTrashed()->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data pendidikan terhapus berhasil diambil',
                'data' => $education
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pendidikan terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
