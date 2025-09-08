<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Occupation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class OccupationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $occupations = Occupation::latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data pekerjaan berhasil diambil',
                'data' => $occupations
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
                'code' => 'nullable|string|max:255',
                'name' => 'required|string|max:255|unique:occupations',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $occupation = Occupation::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pekerjaan berhasil ditambahkan',
                'data' => $occupation
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
                'message' => 'Gagal menambahkan pekerjaan',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan pekerjaan',
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
            $occupation = Occupation::find($id);

            if (!$occupation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pekerjaan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pekerjaan berhasil diambil',
                'data' => $occupation
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $occupation = Occupation::find($id);

            if (!$occupation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pekerjaan tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'nullable|string|max:255',
                'name' => 'required|string|max:255|unique:occupations,name,'.$id,
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $occupation->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pekerjaan berhasil diperbarui',
                'data' => $occupation
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
                'message' => 'Gagal memperbarui pekerjaan',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui pekerjaan',
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
            $occupation = Occupation::find($id);

            if (!$occupation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pekerjaan tidak ditemukan'
                ], 404);
            }

            $occupation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pekerjaan berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pekerjaan',
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
            $occupation = Occupation::withTrashed()->find($id);

            if (!$occupation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pekerjaan tidak ditemukan'
                ], 404);
            }

            if (!$occupation->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pekerjaan tidak dalam keadaan terhapus'
                ], 400);
            }

            $occupation->restore();

            return response()->json([
                'success' => true,
                'message' => 'Pekerjaan berhasil dipulihkan',
                'data' => $occupation
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan pekerjaan',
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
            $occupations = Occupation::onlyTrashed()->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data pekerjaan terhapus berhasil diambil',
                'data' => $occupations
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pekerjaan terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
