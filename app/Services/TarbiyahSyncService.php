<?php

namespace App\Services;

use App\Models\Student;
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
}
