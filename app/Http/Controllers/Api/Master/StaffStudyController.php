<?php

namespace App\Http\Controllers\Api\Master;

use App\Models\Staff;
use App\Models\Study;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StaffStudyController extends Controller
{
    /**
     * Display a listing of staff with their assigned studies
     */
    public function index()
    {
        try {
            $staffWithStudies = Staff::with('studies', 'user')
                ->whereHas('user', function ($query) {
                    $query->role('asatidz');
                })
                ->get();

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $staffWithStudies
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Assign studies to a staff member
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'staff_id' => 'required|exists:staff,id',
                'study_ids' => 'required|array',
                'study_ids.*' => 'exists:studies,id'
            ]);

            $staff = Staff::findOrFail($request->staff_id);

            // Check if the staff member has the 'guru' role
            if (!$staff->user || !$staff->user->hasRole('asatidz')) {
                return response()->json([
                    'message' => 'Staff member does not have the teacher role',
                    'status' => 400,
                    'data' => null
                ], 400);
            }

            // Sync the studies for this staff member
            $staff->studies()->sync($request->study_ids);

            return response()->json([
                'message' => 'Studies assigned successfully',
                'status' => 200,
                'data' => $staff->load('studies')
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Display the specified staff member with their assigned studies
     */
    public function show(string $id)
    {
        try {
            $staff = Staff::with('studies', 'user')
                ->whereHas('user', function ($query) {
                    $query->role('asatidz');
                })
                ->findOrFail($id);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $staff
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Staff not found or does not have teacher role',
                'status' => 404,
                'data' => null
            ], 404);
        }
    }

    /**
     * Update the studies assigned to a staff member
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'study_ids' => 'required|array',
                'study_ids.*' => 'exists:studies,id'
            ]);

            $staff = Staff::findOrFail($id);

            // Check if the staff member has the 'asatidz' role
            if (!$staff->user || !$staff->user->hasRole('asatidz')) {
                return response()->json([
                    'message' => 'Staff member does not have the teacher role',
                    'status' => 400,
                    'data' => null
                ], 400);
            }

            // Sync the studies for this staff member
            $staff->studies()->sync($request->study_ids);

            return response()->json([
                'message' => 'Studies updated successfully',
                'status' => 200,
                'data' => $staff->load('studies')
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove all studies assigned to a staff member
     */
    public function destroy(string $id)
    {
        try {
            $staff = Staff::findOrFail($id);

            // Check if the staff member has the 'guru' role
            if (!$staff->user || !$staff->user->hasRole('asatidz')) {
                return response()->json([
                    'message' => 'Staff member does not have the teacher role',
                    'status' => 400,
                    'data' => null
                ], 400);
            }

            // Remove all study assignments for this staff member
            $staff->studies()->detach();

            return response()->json([
                'message' => 'Studies removed successfully',
                'status' => 200,
                'data' => null
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Get all studies
     */
    public function getAllStudies()
    {
        try {
            $studies = Study::all();

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $studies
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Get all teachers (staff with guru role)
     */
    public function getAllTeachers()
    {
        try {
            $teachers = Staff::with('user')
                ->whereHas('user', function ($query) {
                    $query->role('asatidz');
                })
                ->get();

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $teachers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
                'data' => null
            ], 500);
        }
    }
}
