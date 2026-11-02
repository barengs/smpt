<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use Illuminate\Http\Request;

class InternalStudentSyncController extends Controller
{
    /**
     * Endpoint untuk Tarbiyah menarik (pull) daftar seluruh santri aktif dari SMPT.
     * Dipanggil dengan header X-Internal-Key.
     */
    public function getActiveStudents(Request $request)
    {
        $incomingKey = $request->header('X-Internal-Key');
        $expectedKey = config('services.tarbiyah.internal_key', 'smpt-tarbiyah-internal-secret-2026');

        if (empty($incomingKey) || $incomingKey !== $expectedKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid Internal API Key.',
            ], 401);
        }

        $query = Student::with(['parents'])
            ->where('status', 'Aktif')
            ->whereNotNull('nis');

        if ($request->filled('since')) {
            $query->where('updated_at', '>=', $request->input('since'));
        }

        $students = $query->get()->map(function ($s) {
            $parentName = null;
            if ($s->parents) {
                $parentName = $s->parents->father_name 
                    ?: $s->parents->mother_name 
                    ?: $s->parents->guardian_name;
            }

            return [
                'santri_id'    => $s->id,
                'nis'          => $s->nis,
                'period'       => $s->period, // Angkatan (tahun Hijriah, mis. 1445-1446)
                'program_id'   => $s->program_id ?: null,
                'hostel_id'    => $s->hostel_id ?: null,
                'nik'          => $s->nik,
                'name'         => trim("{$s->first_name} {$s->last_name}"),
                'gender'       => $s->gender ?? 'L',
                'status'       => 'aktif',
                'parent_name'  => $parentName,
                'parent_phone' => $s->phone ?? $s->parents?->phone ?? null,
                'address'      => $s->address,
                'born_in'      => $s->born_in,
                'born_at'      => $s->born_at ? (is_string($s->born_at) ? substr($s->born_at, 0, 10) : $s->born_at->format('Y-m-d')) : null,
                'photo'        => $s->photo,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data santri aktif berhasil diambil',
            'total'   => $students->count(),
            'data'    => $students,
        ], 200);
    }

    /**
     * Endpoint untuk Tarbiyah menarik master Program Pesantren (Tibyan, Kubar, dll) dari SMPT.
     * Dipanggil dengan header X-Internal-Key.
     */
    public function getPrograms(Request $request)
    {
        $incomingKey = $request->header('X-Internal-Key');
        $expectedKey = config('services.tarbiyah.internal_key', 'smpt-tarbiyah-internal-secret-2026');

        if (empty($incomingKey) || $incomingKey !== $expectedKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid Internal API Key.',
            ], 401);
        }

        $programs = Program::all(['id', 'name', 'description']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data program pesantren berhasil diambil',
            'data'    => $programs,
        ], 200);
    }
}
