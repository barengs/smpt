<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\PositionAssignment;
use App\Models\Staff;
use App\Models\Position;
use App\Http\Resources\PositionAssignmentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;

class PositionAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $assignments = PositionAssignment::with(['position.organization', 'staff'])->get();
            return new PositionAssignmentResource('Data penugasan berhasil diambil', $assignments, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'position_id' => 'required|exists:positions,id',
                'staff_id' => 'required|exists:staff,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'assignment_letter' => 'nullable|string',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new PositionAssignmentResource('Validasi gagal', $validator->errors(), 422);
            }

            // Check if staff already has an active assignment
            if ($request->is_active) {
                $existingActiveAssignment = PositionAssignment::where('staff_id', $request->staff_id)
                    ->where('is_active', true)
                    ->first();

                if ($existingActiveAssignment) {
                    return new PositionAssignmentResource('Staff ini sudah memiliki penugasan aktif', null, 409);
                }
            }

            DB::beginTransaction();

            // If this assignment is active, deactivate any previous active assignment for this staff
            if ($request->is_active) {
                PositionAssignment::where('staff_id', $request->staff_id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $assignment = PositionAssignment::create($request->all());
            $assignment->load(['position.organization', 'staff']);

            DB::commit();

            return new PositionAssignmentResource('Penugasan berhasil ditambahkan', $assignment, 201);
        } catch (QueryException $e) {
            DB::rollBack();
            return new PositionAssignmentResource('Gagal menambahkan penugasan', $e->getMessage(), 500);
        } catch (Exception $e) {
            DB::rollBack();
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $assignment = PositionAssignment::with(['position.organization', 'staff'])->findOrFail($id);
            return new PositionAssignmentResource('Data penugasan berhasil diambil', $assignment, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Data penugasan tidak ditemukan', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $assignment = PositionAssignment::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'position_id' => 'required|exists:positions,id',
                'staff_id' => 'required|exists:staff,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'assignment_letter' => 'nullable|string',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new PositionAssignmentResource('Validasi gagal', $validator->errors(), 422);
            }

            // Check if staff already has an active assignment (excluding current assignment)
            if ($request->is_active && $request->staff_id != $assignment->staff_id) {
                $existingActiveAssignment = PositionAssignment::where('staff_id', $request->staff_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingActiveAssignment) {
                    return new PositionAssignmentResource('Staff ini sudah memiliki penugasan aktif', null, 409);
                }
            }

            DB::beginTransaction();

            // If this assignment is being activated, deactivate any previous active assignment for this staff
            if ($request->is_active && !$assignment->is_active) {
                PositionAssignment::where('staff_id', $request->staff_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $id)
                    ->update(['is_active' => false]);
            }

            $assignment->update($request->all());
            $assignment->load(['position.organization', 'staff']);

            DB::commit();

            return new PositionAssignmentResource('Penugasan berhasil diperbarui', $assignment, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $assignment = PositionAssignment::findOrFail($id);
            $assignment->delete();

            return new PositionAssignmentResource('Penugasan berhasil dihapus', null, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get current assignments (active only)
     */
    public function getCurrent()
    {
        try {
            $assignments = PositionAssignment::where('is_active', true)
                ->with(['position.organization', 'staff'])
                ->get();

            return new PositionAssignmentResource('Data penugasan aktif berhasil diambil', $assignments, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get assignments by staff
     */
    public function getByStaff($staffId)
    {
        try {
            $assignments = PositionAssignment::where('staff_id', $staffId)
                ->with(['position.organization', 'staff'])
                ->get();

            return new PositionAssignmentResource('Data penugasan berhasil diambil', $assignments, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get assignments by position
     */
    public function getByPosition($positionId)
    {
        try {
            $assignments = PositionAssignment::where('position_id', $positionId)
                ->with(['position.organization', 'staff'])
                ->get();

            return new PositionAssignmentResource('Data penugasan berhasil diambil', $assignments, 200);
        } catch (Exception $e) {
            return new PositionAssignmentResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}
