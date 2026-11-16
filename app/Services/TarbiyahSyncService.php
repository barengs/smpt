<?php

namespace App\Services;

use App\Models\AcademicQuarter;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TarbiyahSyncService
{
    /**
     * Kirim data santri aktif ke Tarbiyah secara push (service-to-service).
     */
    public static function syncStudent(Student $student): bool
    {
        $tarbiyahUrl = config('services.tarbiyah.url', 'http://127.0.0.1:8002');
        $tarbiyahKey = config('services.tarbiyah.internal_key', 'smpt-tarbiyah-internal-secret-2026');

        if (empty($student->nis)) {
            Log::warning("TarbiyahSync: Santri ID {$student->id} tidak memiliki NIS, sinkronisasi dibatalkan.");
            return false;
        }

        try {
            $student->loadMissing('parents');

            $parentName = null;
            if ($student->parents) {
                $parentName = $student->parents->father_name 
                    ?: $student->parents->mother_name 
                    ?: $student->parents->guardian_name;
            }

            $bornAt = null;
            if ($student->born_at) {
                $bornAt = is_string($student->born_at) 
                    ? substr($student->born_at, 0, 10) 
                    : $student->born_at->format('Y-m-d');
            }

            $response = Http::timeout(5)->withHeaders([
                'X-Internal-Key' => $tarbiyahKey,
                'Accept'         => 'application/json',
                'Content-Type'   => 'application/json',
            ])->post("{$tarbiyahUrl}/api/internal/students/sync", [
                'santri_id'    => $student->id,
                'nis'          => $student->nis,
                'period'       => $student->period ?? null,
                'program_id'   => $student->program_id ?: null,
                'hostel_id'    => $student->hostel_id ?: null,
                'name'         => trim("{$student->first_name} {$student->last_name}"),
                'gender'       => $student->gender ?? 'L',
                'status'       => $student->status ?? 'Aktif',
                'parent_name'  => $parentName,
                'parent_phone' => $student->phone ?? $student->parents?->phone ?? null,
                'address'      => $student->address ?? null,
                'nik'          => $student->nik ?? null,
                'born_in'      => $student->born_in ?? null,
                'born_at'      => $bornAt,
                'photo'        => $student->photo ?? null,
            ]);

            if ($response->successful()) {
                Log::info("TarbiyahSync: Sukses sinkronisasi santri {$student->nis} ke Tarbiyah.");
                return true;
            }

            Log::warning("TarbiyahSync: Gagal sinkronisasi santri {$student->nis}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("TarbiyahSync: Error koneksi ke Tarbiyah untuk NIS {$student->nis}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil kelas aktif (current_class) secara batch dari Tarbiyah berdasarkan daftar NIS.
     * Hasil di-cache singkat per NIS agar daftar santri tetap cepat & aman jika Tarbiyah offline.
     */
    public static function fetchCurrentClasses(array $nisList): array
    {
        $nisList = array_values(array_unique(array_filter($nisList)));
        if (empty($nisList)) {
            return [];
        }

        $result = [];
        $missingNis = [];

        foreach ($nisList as $nis) {
            $cacheKey = "tarbiyah:current_class:{$nis}";
            if (Cache::has($cacheKey)) {
                $cached = Cache::get($cacheKey);
                if ($cached !== null) {
                    $result[$nis] = $cached;
                }
            } else {
                $missingNis[] = $nis;
            }
        }

        if (empty($missingNis)) {
            return $result;
        }

        $tarbiyahUrl = config('services.tarbiyah.url', 'http://127.0.0.1:8002');
        $tarbiyahKey = config('services.tarbiyah.internal_key', 'smpt-tarbiyah-internal-secret-2026');

        try {
            $response = Http::timeout(4)->withHeaders([
                'X-Internal-Key' => $tarbiyahKey,
                'Accept'         => 'application/json',
            ])->post("{$tarbiyahUrl}/api/internal/students/current-classes", [
                'nis_list' => $missingNis,
            ]);

            if ($response->successful()) {
                $fetched = $response->json('data', []);
                foreach ($missingNis as $nis) {
                    $item = $fetched[$nis] ?? null;
                    Cache::put("tarbiyah:current_class:{$nis}", $item, now()->addMinutes(5));
                    if ($item !== null) {
                        $result[$nis] = $item;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('TarbiyahSync: Gagal mengambil batch current_class: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Sinkronisasi master academic_quarters dari Tarbiyah (SSOT) ke SMPT.
     */
    public static function syncAcademicQuarters(): int
    {
        $tarbiyahUrl = config('services.tarbiyah.url', 'http://127.0.0.1:8002');
        $tarbiyahKey = config('services.tarbiyah.internal_key', 'smpt-tarbiyah-internal-secret-2026');

        try {
            $response = Http::timeout(6)->withHeaders([
                'X-Internal-Key' => $tarbiyahKey,
                'Accept'         => 'application/json',
            ])->get("{$tarbiyahUrl}/api/internal/academic-quarters");

            if (! $response->successful()) {
                Log::warning('TarbiyahSync: Gagal menarik academic_quarters, status ' . $response->status());
                return 0;
            }

            $quarters = $response->json('data', []);
            $synced = 0;

            foreach ($quarters as $q) {
                if (empty($q['id']) || empty($q['name'])) {
                    continue;
                }

                $academicYearId = null;
                if (! empty($q['academic_year'])) {
                    $academicYearId = AcademicYear::where('year', $q['academic_year'])->value('id');
                }
                if (! $academicYearId && ! empty($q['academic_year_id'])) {
                    $academicYearId = AcademicYear::where('id', $q['academic_year_id'])->value('id');
                }
                if (! $academicYearId) {
                    continue;
                }

                AcademicQuarter::updateOrCreate(
                    ['id' => $q['id']],
                    [
                        'academic_year_id' => $academicYearId,
                        'name'             => $q['name'],
                        'start_date'       => $q['start_date'] ?? null,
                        'end_date'         => $q['end_date'] ?? null,
                        'active'           => (bool) ($q['active'] ?? false),
                    ]
                );
                $synced++;
            }

            return $synced;
        } catch (\Exception $e) {
            Log::error('TarbiyahSync: Error sync academic_quarters: ' . $e->getMessage());
            return 0;
        }
    }
}
