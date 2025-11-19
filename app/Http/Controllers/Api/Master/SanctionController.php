<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Sanction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class SanctionController extends Controller
{
    /**
     * List sanctions
     */
    public function index()
    {
        try {
            $sanctions = Sanction::orderBy('type')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data sanksi berhasil diambil',
                'data' => $sanctions
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data sanksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a sanction
     *
     * Body:
     * - name: string (required)
     * - description: string (optional)
     * - type: enum(peringatan,skorsing,pembinaan,denda,lainnya) (required)
     * - duration_days: integer (optional)
     * - is_active: boolean (optional)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:peringatan,skorsing,pembinaan,denda,lainnya',
            'duration_days' => 'nullable|integer|min:1',
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
            $sanction = Sanction::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Sanksi berhasil ditambahkan',
                'data' => $sanction
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan sanksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a sanction by ID
     *
     * Path:
     * - id: integer (required)
     */
    public function show(string $id)
    {
        try {
            $sanction = Sanction::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data sanksi berhasil diambil',
                'data' => $sanction
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sanksi tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update a sanction
     *
     * Path:
     * - id: integer (required)
     * Body:
     * - name: string (required)
     * - description: string (optional)
     * - type: enum(peringatan,skorsing,pembinaan,denda,lainnya) (required)
     * - duration_days: integer (optional)
     * - is_active: boolean (optional)
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:peringatan,skorsing,pembinaan,denda,lainnya',
            'duration_days' => 'nullable|integer|min:1',
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
            $sanction = Sanction::findOrFail($id);
            $sanction->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Sanksi berhasil diperbarui',
                'data' => $sanction
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui sanksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a sanction by ID
     *
     * Path:
     * - id: integer (required)
     */
    public function destroy(string $id)
    {
        try {
            $sanction = Sanction::findOrFail($id);
            $sanction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sanksi berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus sanksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
