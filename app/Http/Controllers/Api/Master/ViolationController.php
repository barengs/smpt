<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ViolationController extends Controller
{
    /**
     * List violations
     *
     * Query:
     * - category_id: integer (optional)
     */
    public function index(Request $request)
    {
        try {
            $query = Violation::with('category');

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $violations = $query->orderBy('point', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggaran berhasil diambil',
                'data' => $violations
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a violation
     *
     * Body:
     * - category_id: integer (required)
     * - name: string (required)
     * - description: string (optional)
     * - point: integer (required)
     * - is_active: boolean (optional)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:violation_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'point' => 'required|integer|min:0',
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
            $violation = Violation::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran berhasil ditambahkan',
                'data' => $violation->load('category')
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a violation by ID
     *
     * Path:
     * - id: integer (required)
     */
    public function show(string $id)
    {
        try {
            $violation = Violation::with('category')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggaran berhasil diambil',
                'data' => $violation
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggaran tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update a violation
     *
     * Path:
     * - id: integer (required)
     * Body:
     * - category_id: integer (required)
     * - name: string (required)
     * - description: string (optional)
     * - point: integer (required)
     * - is_active: boolean (optional)
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:violation_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'point' => 'required|integer|min:0',
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
            $violation = Violation::findOrFail($id);
            $violation->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran berhasil diperbarui',
                'data' => $violation->load('category')
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a violation by ID
     *
     * Path:
     * - id: integer (required)
     */
    public function destroy(string $id)
    {
        try {
            $violation = Violation::findOrFail($id);
            $violation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
