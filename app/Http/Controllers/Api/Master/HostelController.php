<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\HostelRequest;
use App\Http\Resources\HostelResource;
use App\Models\Hostel;
use App\Models\PositionAssignment;
use App\Models\Position;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Hostel Management
 *
 * APIs for managing hostels (asrama), including CRUD operations,
 * hostel head (Kepala Asrama) assignments, and assignment history.
 */
class HostelController extends Controller
{
    /**
     * Display a listing of hostels
     *
     * Get all hostels with their program relationship and current hostel head information.
     *
     * @response 200 scenario="Success" {
     *   "message": "Data asrama berhasil diambil",
     *   "status": 200,
     *   "data": [{
     *     "id": 1,
     *     "name": "Asrama Putra A",
     *     "program_id": 1,
     *     "description": "Asrama untuk siswa putra program reguler",
     *     "capacity": 50,
     *     "program": {"id": 1, "name": "Reguler"},
     *     "current_head": {
     *       "id": 1,
     *       "staff_id": 6,
     *       "staff": {"id": 6, "name": "Ahmad Rizki"}
     *     }
     *   }]
     * }
     */
    public function index(): JsonResponse
    {
        try {
            // Get all hostels including soft deleted if needed, with program relation
            $hostels = Hostel::query()
                ->with('program')
                ->get();

            $hostels->transform(function ($hostel) {
                $currentHead = PositionAssignment::with(['staff.user'])
                    ->where('hostel_id', $hostel->id)
                    ->where('is_active', true)
                    ->first();
                $hostel->setRelation('current_head', $currentHead);
                return $hostel;
            });

            return response()->json(new HostelResource('Data asrama berhasil diambil', $hostels, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal mengambil data asrama', null, 500), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HostelRequest $request): JsonResponse
    {
        try {
            $hostel = Hostel::create($request->validated());
            return response()->json(new HostelResource('Asrama berhasil ditambahkan', $hostel, 201), 201);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal menambahkan asrama', null, 500), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $hostel = Hostel::with('program')->findOrFail($id);

            $currentHead = PositionAssignment::with(['staff.user'])
                ->where('hostel_id', $hostel->id)
                ->where('is_active', true)
                ->first();

            $hostel->setRelation('current_head', $currentHead);

            return response()->json(new HostelResource('Data asrama berhasil diambil', $hostel, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Asrama tidak ditemukan', null, 404), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HostelRequest $request, string $id): JsonResponse
    {
        try {

            $hostel = Hostel::findOrFail($id);
            $hostel->update([
                'name' => $request->name,
                'program_id' => $request->program_id ?? $hostel->program_id,
                'description' => $request->description,
                'capacity' => $request->capacity ?? $hostel->capacity, // Keep existing capacity if not provided
            ]);
            return response()->json(new HostelResource('Asrama berhasil diperbarui', $hostel, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal memperbarui asrama', null, 500), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $hostel = Hostel::findOrFail($id);
            $hostel->delete();
            return response()->json(new HostelResource('Asrama berhasil dihapus', null, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal menghapus asrama', null, 500), 500);
        }
    }

    /**
     * Tetapkan Kepala Asrama untuk periode tahun akademik
     *
     * Assigns a staff member as hostel head (Kepala Asrama) for a specific academic year.
     * This endpoint automatically deactivates any previous active assignments for:
     * 1. The hostel (previous hostel head)
     * 2. The staff member (their other active assignments)
     *
     * @param Request $request
     * @param string $id Hostel ID
     * @bodyParam staff_id integer required The staff member ID to assign as hostel head. Must have 'kepala asrama' role. Example: 6
     * @bodyParam academic_year_id integer required The academic year ID for this assignment. Example: 1
     * @bodyParam start_date date required Assignment start date. Format: Y-m-d. Example: 2025-12-13
     * @bodyParam end_date date optional Assignment end date. Must be after start_date. Format: Y-m-d. Example: 2026-12-13
     * @bodyParam notes string optional Additional notes for this assignment. Example: Penempatan semester genap
     *
     * @response 201 scenario="Assignment successful" {
     *   "message": "Kepala Asrama berhasil ditetapkan",
     *   "status": 201,
     *   "data": {
     *     "id": 1,
     *     "staff_id": 6,
     *     "hostel_id": 1,
     *     "academic_year_id": 1,
     *     "start_date": "2025-12-13",
     *     "end_date": "2026-12-13",
     *     "is_active": true,
     *     "staff": {
     *       "id": 6,
     *       "name": "Ahmad Rizki",
     *       "user": {
     *         "id": 10,
     *         "name": "Ahmad Rizki"
     *       }
     *     }
     *   }
     * }
     *
     * @response 422 scenario="Staff doesn't have kepala asrama role" {
     *   "message": "Staff tidak memiliki role Kepala Asrama",
     *   "status": 422
     * }
     *
     * @response 404 scenario="Hostel not found" {
     *   "message": "Asrama tidak ditemukan",
     *   "status": 404
     * }
     *
     * @response 500 scenario="Server error" {
     *   "message": "Gagal menetapkan Kepala Asrama",
     *   "status": 500,
     *   "data": "Error message"
     * }
     */
    public function assignHead(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $hostel = Hostel::findOrFail($id);
            $staff = Staff::with('user')->findOrFail($validated['staff_id']);

            // Validasi: Cek apakah staff memiliki role 'Kepala Asrama'
            if (!$staff->user || !$staff->user->hasRole('kepala asrama')) {
                return response()->json([
                    'message' => 'Staff tidak memiliki role Kepala Asrama',
                    'status' => 422
                ], 422);
            }

            DB::beginTransaction();

            // 1. Nonaktifkan kepala asrama aktif sebelumnya untuk hostel ini
            PositionAssignment::where('hostel_id', $hostel->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => $validated['start_date'],
                ]);

            // 2. Nonaktifkan assignment aktif untuk staff ini di hostel yang SAMA
            //    (mencegah duplicate entry untuk combination staff_id + hostel_id + academic_year_id)
            PositionAssignment::where('staff_id', $validated['staff_id'])
                ->where('hostel_id', $hostel->id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => $validated['start_date'],
                ]);

            // 3. Buat assignment baru tanpa position_id
            $assignment = PositionAssignment::create([
                'position_id' => null,
                'staff_id' => $validated['staff_id'],
                'hostel_id' => $hostel->id,
                'academic_year_id' => $validated['academic_year_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'is_active' => true,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return response()->json(new HostelResource('Kepala Asrama berhasil ditetapkan', $assignment->load('staff.user'), 201), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new HostelResource('Gagal menetapkan Kepala Asrama', $e->getMessage(), 500), 500);
        }
    }

    /**
     * Get current hostel head
     *
     * Retrieve the current active hostel head (Kepala Asrama) for a specific hostel,
     * optionally filtered by academic year.
     *
     * @param Request $request
     * @param string $id Hostel ID
     * @queryParam academic_year_id integer optional Filter by specific academic year. Example: 1
     *
     * @response 200 scenario="Current head found" {
     *   "message": "Kepala Asrama saat ini",
     *   "status": 200,
     *   "data": {
     *     "id": 1,
     *     "staff_id": 6,
     *     "hostel_id": 1,
     *     "academic_year_id": 1,
     *     "is_active": true,
     *     "staff": {
     *       "id": 6,
     *       "name": "Ahmad Rizki",
     *       "user": {"id": 10, "name": "Ahmad Rizki"}
     *     }
     *   }
     * }
     *
     * @response 200 scenario="No current head" {
     *   "message": "Kepala Asrama saat ini",
     *   "status": 200,
     *   "data": null
     * }
     */
    public function currentHead(Request $request, string $id): JsonResponse
    {
        $hostel = Hostel::findOrFail($id);
        $academicYearId = $request->query('academic_year_id');

        $query = PositionAssignment::with(['staff.user'])
            ->where('hostel_id', $hostel->id)
            ->where('is_active', true);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $current = $query->first();

        return response()->json(new HostelResource('Kepala Asrama saat ini', $current, 200), 200);
    }

    /**
     * Get hostel head assignment history
     *
     * Retrieve the complete history of hostel head assignments for a specific hostel,
     * including both active and inactive assignments, ordered by start date (newest first).
     *
     * @param string $id Hostel ID
     *
     * @response 200 scenario="Success" {
     *   "message": "Riwayat Kepala Asrama",
     *   "status": 200,
     *   "data": [{
     *     "id": 1,
     *     "staff_id": 6,
     *     "hostel_id": 1,
     *     "academic_year_id": 1,
     *     "start_date": "2025-12-13",
     *     "end_date": "2026-12-13",
     *     "is_active": true,
     *     "staff": {"id": 6, "name": "Ahmad Rizki"},
     *     "academicYear": {"id": 1, "year": "2025/2026"}
     *   }]
     * }
     */
    public function headHistory(string $id): JsonResponse
    {
        $hostel = Hostel::findOrFail($id);

        $history = PositionAssignment::with(['staff.user', 'academicYear'])
            ->where('hostel_id', $hostel->id)
            ->orderByDesc('start_date')
            ->get();

        return response()->json(new HostelResource('Riwayat Kepala Asrama', $history, 200), 200);
    }

    /**
     * Get all staff with Kepala Asrama role
     *
     * Retrieve all staff members who have the 'kepala asrama' (hostel head) role.
     * Useful for populating dropdown/select options when assigning hostel heads.
     *
     * @response 200 scenario="Success" {
     *   "message": "Data staff Kepala Asrama berhasil diambil",
     *   "status": 200,
     *   "data": [{
     *     "id": 6,
     *     "nik": "123456789",
     *     "first_name": "Ahmad",
     *     "last_name": "Rizki",
     *     "user": {
     *       "id": 10,
     *       "name": "Ahmad Rizki",
     *       "email": "ahmad@example.com"
     *     }
     *   }]
     * }
     *
     * @response 500 scenario="Server error" {
     *   "message": "Gagal mengambil data staff Kepala Asrama",
     *   "status": 500,
     *   "data": null
     * }
     */
    public function getHeadStaff(): JsonResponse
    {
        try {
            $headStaff = Staff::with(['user' => function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'kepala asrama');
                });
            }])
            ->whereHas('user.roles', function ($query) {
                $query->where('name', 'kepala asrama');
            })
            ->get();

            return response()->json(new HostelResource('Data staff Kepala Asrama berhasil diambil', $headStaff, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal mengambil data staff Kepala Asrama', null, 500), 500);
        }
    }
}
