<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ClassGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $classGroups = ClassGroup::with('classroom')->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data kelompok kelas berhasil diambil',
                'data' => $classGroups
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kelompok kelas',
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
                'classroom_id' => 'required|exists:classrooms,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $classGroup = ClassGroup::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kelompok kelas berhasil ditambahkan',
                'data' => $classGroup
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
                'message' => 'Gagal menambahkan kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan kelompok kelas',
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
            $classGroup = ClassGroup::with('classroom')->find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data kelompok kelas berhasil diambil',
                'data' => $classGroup
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kelompok kelas',
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
            $classGroup = ClassGroup::find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'classroom_id' => 'required|exists:classrooms,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $classGroup->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kelompok kelas berhasil diperbarui',
                'data' => $classGroup
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
                'message' => 'Gagal memperbarui kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui kelompok kelas',
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
            $classGroup = ClassGroup::find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            $classGroup->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kelompok kelas berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kelompok kelas',
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
            $classGroup = ClassGroup::withTrashed()->find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            if (!$classGroup->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak dalam keadaan terhapus'
                ], 400);
            }

            $classGroup->restore();

            return response()->json([
                'success' => true,
                'message' => 'Kelompok kelas berhasil dipulihkan',
                'data' => $classGroup
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan kelompok kelas',
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
            $classGroups = ClassGroup::with('classroom')->onlyTrashed()->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data kelompok kelas terhapus berhasil diambil',
                'data' => $classGroups
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kelompok kelas terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
