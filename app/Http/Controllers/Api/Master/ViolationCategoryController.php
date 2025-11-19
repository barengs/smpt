<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\ViolationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ViolationCategoryController extends Controller
{
    /**
     * List violation categories
     *
     * Returns all violation categories with violation counts.
     */
    public function index()
    {
        try {
            $categories = ViolationCategory::withCount('violations')
                ->orderBy('severity_level')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data kategori pelanggaran berhasil diambil',
                'data' => $categories
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a violation category
     *
     * Body:
     * - name: string (required)
     * - description: string (optional)
     * - severity_level: integer 1..3 (required)
     * - is_active: boolean (optional)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'severity_level' => 'required|integer|min:1|max:3',
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
            $category = ViolationCategory::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kategori pelanggaran berhasil ditambahkan',
                'data' => $category
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kategori pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a violation category by ID
     *
     * Path:
     * - id: integer (required)
     */
    public function show(string $id)
    {
        try {
            $category = ViolationCategory::with('violations')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data kategori pelanggaran berhasil diambil',
                'data' => $category
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori pelanggaran tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update a violation category
     *
     * Path:
     * - id: integer (required)
     * Body:
     * - name: string (required)
     * - description: string (optional)
     * - severity_level: integer 1..3 (required)
     * - is_active: boolean (optional)
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'severity_level' => 'required|integer|min:1|max:3',
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
            $category = ViolationCategory::findOrFail($id);
            $category->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kategori pelanggaran berhasil diperbarui',
                'data' => $category
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a violation category by ID
     *
     * Path:
     * - id: integer (required)
     */
    public function destroy(string $id)
    {
        try {
            $category = ViolationCategory::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori pelanggaran berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
