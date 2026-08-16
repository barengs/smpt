<?php

namespace App\Http\Controllers\Api\Master;

use App\Models\Staff;
use App\Models\Study;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StaffStudyController extends Controller
{
    /**
     * Display a listing of staff with their assigned studies.
     *
     * Filter guru yang terkait dengan satu institusi pendidikan tertentu.
     * Sumber keterkaitan guru ke institusi ada DUA jalur:
     *   Jalur 1 — Direct pivot: staff_educational_institutions
     *             (di-sync otomatis oleh PositionAssignment::booted)
     *   Jalur 2 — Struktur Organisasi: position_assignments (is_active=true)
     *             → positions → organizations (educational_institution_id)
     *
     * Menggunakan union kedua jalur agar data lama (sebelum auto-sync)
     * tetap terjangkau tanpa perlu migrasi data.
     *
     * @queryParam educational_institution_id int Filter berdasarkan institusi pendidikan.
     */
    public function index(Request $request)
    {
        try {
            $query = Staff::with('studies', 'user.roles', 'educationalInstitutions')
                ->whereHas('user', function ($q) {
                    $q->role(['asatidz', 'walikelas']);
                });

            if ($request->filled('educational_institution_id')) {
                $instId = (int) $request->educational_institution_id;

                $query->where(function ($q) use ($instId) {
                    // Jalur 1: pivot staff_educational_institutions (hasil auto-sync PositionAssignment)
                    $q->whereHas('educationalInstitutions', function ($sq) use ($instId) {
                        $sq->where('educational_institutions.id', $instId);
                    })
                    // Jalur 2: PositionAssignment aktif → Position → Organization → institusi
                    ->orWhereHas('assignments', function ($sq) use ($instId) {
                        $sq->where('is_active', true)
                            ->whereHas('position.organization', function ($osq) use ($instId) {
                                $osq->where('educational_institution_id', $instId);
                            });
                    });
                });
            }

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $query->get()
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

            // Check if the staff member has the 'asatidz' or 'walikelas' role
            if (!$staff->user || (!$staff->user->hasRole('asatidz') && !$staff->user->hasRole('walikelas'))) {
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
            $staff = Staff::with('studies', 'user', 'educationalInstitutions')
                ->whereHas('user', function ($query) {
                    $query->role(['asatidz', 'walikelas']);
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

            // Check if the staff member has the 'asatidz' or 'walikelas' role
            if (!$staff->user || (!$staff->user->hasRole('asatidz') && !$staff->user->hasRole('walikelas'))) {
                return response()->json([
                    'message' => 'Staff member does not have the asatidz or walikelas role',
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

            // Check if the staff member has the 'asatidz' or 'walikelas' role
            if (!$staff->user || (!$staff->user->hasRole('asatidz') && !$staff->user->hasRole('walikelas'))) {
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
            $teachers = Staff::with('user', 'educationalInstitutions')
                ->whereHas('user', function ($query) {
                    $query->role(['asatidz', 'walikelas']);
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
