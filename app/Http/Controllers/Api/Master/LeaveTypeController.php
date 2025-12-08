<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class LeaveTypeController extends Controller
{
    /**
     * List all leave types
     *
     * Query params:
     * - is_active: boolean (optional)
     */
    public function index(Request $request)
    {
        try {
            $query = LeaveType::query();

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $leaveTypes = $query->orderBy('name')->get();

            return LeaveTypeResource::collection($leaveTypes);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jenis izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new leave type
     *
     * Body:
     * - name: string (required)
     * - description: string (optional)
     * - requires_approval: boolean (optional, default true)
     * - max_duration_days: integer (optional)
     * - is_active: boolean (optional, default true)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:leave_types,name',
            'description' => 'nullable|string',
            'requires_approval' => 'nullable|boolean',
            'max_duration_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama jenis izin harus diisi',
            'name.unique' => 'Jenis izin sudah ada',
            'max_duration_days.min' => 'Durasi maksimal minimal 1 hari',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $leaveType = LeaveType::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jenis izin berhasil dibuat',
                'data' => new LeaveTypeResource($leaveType)
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat jenis izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show leave type detail
     *
     * Path:
     * - id: integer (required)
     */
    public function show(string $id)
    {
        try {
            $leaveType = LeaveType::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new LeaveTypeResource($leaveType)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis izin tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update leave type
     *
     * Path:
     * - id: integer (required)
     * Body:
     * - name: string (optional)
     * - description: string (optional)
     * - requires_approval: boolean (optional)
     * - max_duration_days: integer (optional)
     * - is_active: boolean (optional)
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:leave_types,name,' . $id,
            'description' => 'nullable|string',
            'requires_approval' => 'nullable|boolean',
            'max_duration_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $leaveType = LeaveType::findOrFail($id);

            DB::beginTransaction();

            $leaveType->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jenis izin berhasil diperbarui',
                'data' => new LeaveTypeResource($leaveType)
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jenis izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete leave type
     *
     * Path:
     * - id: integer (required)
     */
    public function destroy(string $id)
    {
        try {
            $leaveType = LeaveType::findOrFail($id);

            // Check if leave type is being used
            if ($leaveType->studentLeaves()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis izin tidak dapat dihapus karena sedang digunakan'
                ], 422);
            }

            DB::beginTransaction();
            $leaveType->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jenis izin berhasil dihapus'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jenis izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
