<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                $parentName = trim("{$s->parents->first_name} {$s->parents->last_name}") ?: null;
            }

            return [
                'santri_id'    => $s->id,
                'parent_id'    => $s->parent_id ?: $s->parents?->nik ?: null,
                'kk'           => $s->kk ?? $s->parents?->kk ?? null,
                'nis'          => $s->nis,
                'period'       => $s->period, // Angkatan (tahun Hijriah, mis. 1445-1446)
                'program_id'   => $s->program_id ?: null,
                'hostel_id'    => $s->hostel_id ?: null,
                'nik'          => $s->nik,
                'name'         => trim("{$s->first_name} {$s->last_name}"),
                'gender'       => $s->gender ?? 'L',
                'status'       => 'aktif',
                'phone'        => $s->phone ?? null,
                'parent_name'  => $parentName,
                'parent_phone' => $s->parents?->phone ?? $s->phone ?? null,
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

    /**
     * Endpoint untuk Tarbiyah menarik master Tahun Akademik dari SMPT.
     * Dipanggil dengan header X-Internal-Key.
     */
    public function getAcademicYears(Request $request)
    {
        $incomingKey = $request->header('X-Internal-Key');
        $expectedKey = config('services.tarbiyah.internal_key', 'smpt-tarbiyah-internal-secret-2026');

        if (empty($incomingKey) || $incomingKey !== $expectedKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid Internal API Key.',
            ], 401);
        }

        $years = \App\Models\AcademicYear::orderBy('year', 'desc')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data tahun akademik berhasil diambil',
            'data'    => $years,
        ], 200);
    }

    /**
     * Endpoint untuk Tarbiyah menarik (pull) daftar staf dengan role guru/asatidz/walikelas dari SMPT.
     * Dipanggil dengan header X-Internal-Key.
     */
    public function getTeachers(Request $request)
    {
        $incomingKey = $request->header('X-Internal-Key');
        $expectedKey = config('services.tarbiyah.internal_key', 'smpt-tarbiyah-internal-secret-2026');

        if (empty($incomingKey) || $incomingKey !== $expectedKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid Internal API Key.',
            ], 401);
        }

        $staffList = Staff::with(['user.roles'])
            ->whereHas('user.roles', function ($query) {
                $query->whereIn('name', ['guru', 'asatidz', 'walikelas']);
            })
            ->orderBy('first_name')
            ->get()
            ->map(function ($s) {
                $rawGender = strtoupper(trim($s->gender ?? 'L'));
                $gender = ($rawGender === 'P' || $rawGender === 'PEREMPUAN') ? 'P' : 'L';

                return [
                    'staff_id'  => $s->id,
                    'nip'       => $s->nip ?: $s->code ?: null,
                    'name'      => trim("{$s->first_name} {$s->last_name}"),
                    'gender'    => $gender,
                    'phone'     => $s->phone,
                    'email'     => $s->email ?: $s->user?->email,
                    'status'    => 'tetap',
                    'is_active' => strtolower($s->status ?? 'aktif') === 'aktif',
                    'roles'     => $s->user?->roles->pluck('name')->values() ?? [],
                ];
            });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data guru/staf berhasil diambil',
            'total'   => $staffList->count(),
            'data'    => $staffList,
        ], 200);
    }

    /**
     * Endpoint untuk Tarbiyah menarik riwayat penempatan kelas lama dari SMPT
     * (jika tabel student_classes & class_groups masih tersedia di database SMPT).
     */
    public function getLegacyStudentClasses(Request $request)
    {
        $incomingKey = $request->header('X-Internal-Key');
        $expectedKey = config('services.tarbiyah.internal_key', 'smpt-tarbiyah-internal-secret-2026');

        if (empty($incomingKey) || $incomingKey !== $expectedKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid Internal API Key.',
            ], 401);
        }

        if (! Schema::hasTable('student_classes') || ! Schema::hasTable('class_groups')) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Tabel kelas lama (student_classes/class_groups) sudah dihapus dari SMPT.',
                'total'   => 0,
                'data'    => [],
            ], 200);
        }

        $rows = DB::table('student_classes as sc')
            ->join('students as s', 's.id', '=', 'sc.student_id')
            ->join('class_groups as cg', 'cg.id', '=', 'sc.class_group_id')
            ->leftJoin('academic_years as ay', 'ay.id', '=', 'sc.academic_year_id')
            ->whereNotNull('s.nis')
            ->select([
                's.nis',
                'cg.name as class_group_name',
                'ay.year as academic_year',
                'sc.approval_status',
                'sc.created_at',
            ])
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data penempatan kelas lama berhasil diambil',
            'total'   => $rows->count(),
            'data'    => $rows,
        ], 200);
    }
}
