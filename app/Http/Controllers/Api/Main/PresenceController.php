<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presence;
use App\Http\Requests\PresenceRequest;
use App\Http\Resources\PresenceResource;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PresenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Presence::with(['student', 'meetingSchedule', 'user']);

            // Filter by student if provided
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            // Filter by meeting schedule if provided
            if ($request->has('meeting_schedule_id')) {
                $query->where('meeting_schedule_id', $request->meeting_schedule_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date if provided
            if ($request->has('date')) {
                $query->where('date', $request->date);
            }

            // Filter by user if provided
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $presences = $query->paginate($request->get('per_page', 15));

            return new PresenceResource('Data presensi berhasil diambil', $presences, 200);
        } catch (QueryException $e) {
            Log::error('Database error while fetching presences: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat mengambil data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while fetching presences: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat mengambil data presensi', null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PresenceRequest $request)
    {
        try {
            $presence = Presence::create($request->validated());

            // Load relationships
            $presence->load(['student', 'meetingSchedule', 'user']);

            return new PresenceResource('Data presensi berhasil disimpan', $presence, 201);
        } catch (ValidationException $e) {
            return new PresenceResource('Validasi gagal', $e->errors(), 422);
        } catch (QueryException $e) {
            Log::error('Database error while creating presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat menyimpan data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while creating presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat menyimpan data presensi', null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $presence = Presence::with(['student', 'meetingSchedule', 'user'])->findOrFail($id);

            return new PresenceResource('Data presensi berhasil diambil', $presence, 200);
        } catch (ModelNotFoundException $e) {
            return new PresenceResource('Data presensi tidak ditemukan', null, 404);
        } catch (QueryException $e) {
            Log::error('Database error while fetching presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat mengambil data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while fetching presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat mengambil data presensi', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PresenceRequest $request, string $id)
    {
        try {
            $presence = Presence::findOrFail($id);

            $presence->update($request->validated());

            // Load relationships
            $presence->load(['student', 'meetingSchedule', 'user']);

            return new PresenceResource('Data presensi berhasil diperbarui', $presence, 200);
        } catch (ModelNotFoundException $e) {
            return new PresenceResource('Data presensi tidak ditemukan', null, 404);
        } catch (ValidationException $e) {
            return new PresenceResource('Validasi gagal', $e->errors(), 422);
        } catch (QueryException $e) {
            Log::error('Database error while updating presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat memperbarui data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while updating presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat memperbarui data presensi', null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $presence = Presence::findOrFail($id);
            $presence->delete();

            return new PresenceResource('Data presensi berhasil dihapus', null, 200);
        } catch (ModelNotFoundException $e) {
            return new PresenceResource('Data presensi tidak ditemukan', null, 404);
        } catch (QueryException $e) {
            Log::error('Database error while deleting presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat menghapus data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while deleting presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat menghapus data presensi', null, 500);
        }
    }

    /**
     * Get presence statistics by status
     */
    public function statistics(Request $request)
    {
        try {
            $query = Presence::query();

            // Filter by student if provided
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            // Filter by meeting schedule if provided
            if ($request->has('meeting_schedule_id')) {
                $query->where('meeting_schedule_id', $request->meeting_schedule_id);
            }

            // Filter by date if provided
            if ($request->has('date')) {
                $query->where('date', $request->date);
            }

            // Filter by user if provided
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Get count for each status
            $hadir = (clone $query)->where('status', 'hadir')->count();
            $izin = (clone $query)->where('status', 'izin')->count();
            $sakit = (clone $query)->where('status', 'sakit')->count();
            $alpha = (clone $query)->where('status', 'alpha')->count();
            $total = $hadir + $izin + $sakit + $alpha;

            $statistics = [
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
                'total' => $total,
                'percentages' => [
                    'hadir' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0,
                    'izin' => $total > 0 ? round(($izin / $total) * 100, 2) : 0,
                    'sakit' => $total > 0 ? round(($sakit / $total) * 100, 2) : 0,
                    'alpha' => $total > 0 ? round(($alpha / $total) * 100, 2) : 0,
                ]
            ];

            return new PresenceResource('Statistik presensi berhasil diambil', $statistics, 200);
        } catch (QueryException $e) {
            Log::error('Database error while fetching presence statistics: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat mengambil statistik presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while fetching presence statistics: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat mengambil statistik presensi', null, 500);
        }
    }
}
