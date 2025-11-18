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
            $hostels = Hostel::with('program')->paginate(10);
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
            $hostel = Hostel::findOrFail($id);
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
            'position_id' => 'required|exists:positions,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $hostel = Hostel::findOrFail($id);

        DB::beginTransaction();
        try {
            PositionAssignment::where('hostel_id', $hostel->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => $validated['start_date'],
                ]);

            $assignment = PositionAssignment::create([
                'position_id' => $validated['position_id'],
                'staff_id' => $validated['staff_id'],
                'hostel_id' => $hostel->id,
                'academic_year_id' => $validated['academic_year_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'is_active' => true,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return response()->json(new HostelResource('Kepala Asrama berhasil ditetapkan', $assignment->load(['position', 'staff']), 201), 201);
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

        $query = PositionAssignment::with(['position', 'staff'])
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

        $history = PositionAssignment::with(['position', 'staff'])
            ->where('hostel_id', $hostel->id)
            ->orderByDesc('start_date')
            ->get();

        return response()->json(new HostelResource('Riwayat Kepala Asrama', $history, 200), 200);
    }
}
