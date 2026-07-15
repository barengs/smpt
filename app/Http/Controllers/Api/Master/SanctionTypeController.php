<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SanctionType;
use Illuminate\Support\Facades\Validator;
use Exception;

class SanctionTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $sanctionTypes = SanctionType::orderBy('name', 'asc')->get();
            return response()->json([
                'success' => true,
                'message' => 'Daftar jenis sanksi berhasil diambil',
                'data' => $sanctionTypes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar jenis sanksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sanctionType = SanctionType::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Jenis sanksi berhasil ditambahkan',
                'data' => $sanctionType
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan jenis sanksi',
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
            $sanctionType = SanctionType::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Data jenis sanksi berhasil diambil',
                'data' => $sanctionType
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis sanksi tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sanctionType = SanctionType::findOrFail($id);
            $sanctionType->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Jenis sanksi berhasil diperbarui',
                'data' => $sanctionType
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui jenis sanksi',
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
            $sanctionType = SanctionType::findOrFail($id);
            
            // Check if there are related sanctions
            if ($sanctionType->sanctions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus jenis sanksi karena sudah digunakan pada data sanksi',
                ], 422);
            }

            $sanctionType->delete();
            return response()->json([
                'success' => true,
                'message' => 'Jenis sanksi berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus jenis sanksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
