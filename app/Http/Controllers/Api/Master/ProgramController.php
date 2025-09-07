<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $programs = Program::all();
            return response()->json([
                'success' => true,
                'data' => $programs
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data program: ' . $e->getMessage()
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

            $program = Program::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Program berhasil dibuat',
                'data' => $program
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
                'message' => 'Gagal menyimpan program: ' . $e->getMessage()
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
            $program = Program::find($id);

            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $program
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data program: ' . $e->getMessage()
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
            $program = Program::find($id);

            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program tidak ditemukan'
                ], 404);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string'
            ]);

            $program->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Program berhasil diperbarui',
                'data' => $program
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
                'message' => 'Gagal memperbarui program: ' . $e->getMessage()
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
            $program = Program::find($id);

            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program tidak ditemukan'
                ], 404);
            }

            $program->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program berhasil dihapus'
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus program: ' . $e->getMessage()
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
            // Mencari program yang telah dihapus
            $program = Program::withTrashed()->find($id);

            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program tidak ditemukan'
                ], 404);
            }

            // Memastikan program dalam keadaan terhapus
            if (!$program->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program tidak dalam keadaan terhapus'
                ], 400);
            }

            // Memulihkan program
            $program->restore();

            return response()->json([
                'success' => true,
                'message' => 'Program berhasil dipulihkan',
                'data' => $program
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan program: ' . $e->getMessage()
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
            $trashedPrograms = Program::onlyTrashed()->get();

            return response()->json([
                'success' => true,
                'data' => $trashedPrograms
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data program yang terhapus: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
