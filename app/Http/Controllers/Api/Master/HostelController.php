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

class HostelController extends Controller
{
    /**
     * Display a listing of the resource.
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

            // Nonaktifkan kepala asrama aktif sebelumnya
            PositionAssignment::where('hostel_id', $hostel->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => $validated['start_date'],
                ]);

            // Buat assignment baru tanpa position_id
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
     * Kepala Asrama saat ini
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
     * Riwayat Kepala Asrama
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
}
