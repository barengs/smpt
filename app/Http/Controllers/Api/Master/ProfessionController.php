<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Profession;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;

class ProfessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $professions = Profession::all();
            return response()->json([
                'success' => true,
                'data' => $professions
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data profesi: ' . $e->getMessage()
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

            $profession = Profession::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Profesi berhasil dibuat',
                'data' => $profession
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
                'message' => 'Gagal menyimpan profesi: ' . $e->getMessage()
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
            $profession = Profession::find($id);

            if (!$profession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesi tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $profession
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data profesi: ' . $e->getMessage()
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
            $profession = Profession::find($id);

            if (!$profession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesi tidak ditemukan'
                ], 404);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string'
            ]);

            $profession->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Profesi berhasil diperbarui',
                'data' => $profession
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
                'message' => 'Gagal memperbarui profesi: ' . $e->getMessage()
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
            $profession = Profession::find($id);

            if (!$profession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesi tidak ditemukan'
                ], 404);
            }

            $profession->delete();

            return response()->json([
                'success' => true,
                'message' => 'Profesi berhasil dihapus'
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus profesi: ' . $e->getMessage()
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
            // Mencari profesi yang telah dihapus
            $profession = Profession::withTrashed()->find($id);

            if (!$profession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesi tidak ditemukan'
                ], 404);
            }

            // Memastikan profesi dalam keadaan terhapus
            if (!$profession->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesi tidak dalam keadaan terhapus'
                ], 400);
            }

            // Memulihkan profesi
            $profession->restore();

            return response()->json([
                'success' => true,
                'message' => 'Profesi berhasil dipulihkan',
                'data' => $profession
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan profesi: ' . $e->getMessage()
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
            $trashedProfessions = Profession::onlyTrashed()->get();

            return response()->json([
                'success' => true,
                'data' => $trashedProfessions
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data profesi yang terhapus: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
