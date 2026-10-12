<?php

namespace App\Services;

use App\Models\AcademicQuarter;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Exam Clearance Service (SMPT)
 *
 * Menentukan kelayakan santri mengikuti ujian triwulan berdasarkan
 * status keuangan (tunggakan) dari bank-santri.
 *
 * PENTING: Kebijakan bisnis (batas toleransi tunggakan, dispensasi)
 * berada di SMPT — domain pendidikan. Bank-santri hanya menyajikan
 * FAKTA finansial obyektif (berapa tunggakan, periode apa saja).
 */
class ExamClearanceService
{
    /** Batas tunggakan maksimal (dalam bulan) sebelum santri diblokir ujian. */
    protected int $maxOverdueMonths;
    /** Toleransi nominal tunggakan (Rp) sebelum blokir. */
    protected float $maxArrearsAmount;
    /** Izinkan dispensasi manual oleh panitia/kepala sekolah. */
    protected bool $allowDispensation;

    public function __construct()
    {
        $this->maxOverdueMonths   = (int) (config('exam_clearance.max_overdue_months', 1));
        $this->maxArrearsAmount   = (float) (config('exam_clearance.max_arrears_amount', 0));
        $this->allowDispensation  = (bool) (config('exam_clearance.allow_dispensation', true));
    }

    /**
     * Ambil ringkasan tunggakan santri dari bank-santri.
     */
    public function fetchArrears(string $nis): ?array
    {
        $bankUrl = config('services.bank_santri.url');
        $bankKey = config('services.bank_santri.internal_key');

        try {
            $res = Http::timeout(8)->withHeaders([
                'X-Internal-Key' => $bankKey,
                'Accept'         => 'application/json',
            ])->get("{$bankUrl}/api/internal/account/{$nis}/arrears");

            if ($res->successful()) {
                return $res->json('data');
            }

            Log::warning("ExamClearance: gagal ambil arrears dari bank-santri", [
                'nis'    => $nis,
                'status' => $res->status(),
            ]);
        } catch (\Exception $e) {
            Log::error("ExamClearance: error koneksi bank-santri", [
                'nis'   => $nis,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Evaluasi kelayakan ujian satu santri.
     *
     * Return:
     *  - eligible: bool   → boleh ikut ujian atau tidak
     *  - status: string   → eligible | dispensation | blocked | unknown
     *  - reason: string   → penjelasan untuk ditampilkan di UI
     */
    public function check(Student $student, ?AcademicQuarter $quarter = null): array
    {
        $quarter = $quarter ?? AcademicQuarter::where('active', true)->first();

        $base = [
            'student_id' => $student->id,
            'nis'        => $student->nis,
            'name'       => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
            'quarter'    => $quarter ? [
                'id'         => $quarter->id,
                'name'       => $quarter->name,
                'start_date' => optional($quarter->start_date)->toDateString(),
                'end_date'   => optional($quarter->end_date)->toDateString(),
            ] : null,
        ];

        if (!$student->nis) {
            return array_merge($base, [
                'eligible' => true,
                'status'   => 'eligible',
                'reason'   => 'Santri belum memiliki NIS rekening — tidak ada cek keuangan.',
                'arrears'  => null,
            ]);
        }

        $arrears = $this->fetchArrears($student->nis);

        // Bank tidak terjangkau → jangan blokir ujian (fail-open),
        // tapi tandai perlu verifikasi manual.
        if ($arrears === null) {
            return array_merge($base, [
                'eligible' => true,
                'status'   => 'unknown',
                'reason'   => 'Data keuangan tidak tersedia (bank-santri tidak terjangkau). Verifikasi manual diperlukan.',
                'arrears'  => null,
            ]);
        }

        $hasArrears   = (bool) ($arrears['has_arrears'] ?? false);
        $total        = (float) ($arrears['total_arrears'] ?? 0);
        $overdueCount = (int) ($arrears['overdue_months_count'] ?? 0);

        // 1. Tidak ada tunggakan → lolos
        if (!$hasArrears) {
            return array_merge($base, [
                'eligible' => true,
                'status'   => 'eligible',
                'reason'   => 'Tidak ada tunggakan — kelayakan ujian terpenuhi.',
                'arrears'  => $arrears,
            ]);
        }

        // 2. Tunggakan masih dalam toleransi kebijakan
        if ($overdueCount <= $this->maxOverdueMonths && $total <= $this->maxArrearsAmount) {
            return array_merge($base, [
                'eligible' => true,
                'status'   => 'eligible',
                'reason'   => sprintf(
                    'Tunggakan Rp %s (%d bulan) masih dalam toleransi kebijakan.',
                    number_format($total, 0, ',', '.'),
                    $overdueCount
                ),
                'arrears'  => $arrears,
            ]);
        }

        // 3. Lewat toleransi → cek dispensasi aktif
        if ($this->allowDispensation) {
            $dispensation = $this->activeDispensation($student->id, $quarter);
            if ($dispensation) {
                return array_merge($base, [
                    'eligible'    => true,
                    'status'      => 'dispensation',
                    'reason'      => "Menunggak Rp " . number_format($total, 0, ',', '.')
                        . " ({$overdueCount} bulan) — diizinkan dengan dispensasi sampai "
                        . optional($dispensation['until'])->toDateString() . ".",
                    'arrears'     => $arrears,
                    'dispensation'=> $dispensation,
                ]);
            }
        }

        // 4. Diblokir
        return array_merge($base, [
            'eligible' => false,
            'status'   => 'blocked',
            'reason'   => sprintf(
                'DIBLOKIR: Menunggak Rp %s (%d bulan, periode %s s.d %s). Lunasi atau ajukan dispensasi.',
                number_format($total, 0, ',', '.'),
                $overdueCount,
                $arrears['oldest_overdue_period'] ?? '-',
                $arrears['newest_overdue_period'] ?? '-'
            ),
            'arrears'  => $arrears,
        ]);
    }

    /**
     * Dispensasi ujian aktif (surat perjanjian bayar / izin kepala sekolah).
     * Menumpang pada tabel student_agreements dengan catatan dispensasi ujian.
     */
    public function activeDispensation(int $studentId, ?AcademicQuarter $quarter): ?array
    {
        $dispensation = \App\Models\StudentExamDispensation::where('student_id', $studentId)
            ->where('status', 'active')
            ->where(function ($q) use ($quarter) {
                if ($quarter) {
                    $q->where('academic_quarter_id', $quarter->id)
                      ->orWhereNull('academic_quarter_id');
                }
            })
            ->orderByDesc('created_at')
            ->first();

        if (!$dispensation) {
            return null;
        }

        return [
            'id'       => $dispensation->id,
            'until'    => $dispensation->commitment_date,
            'granted_by' => $dispensation->granted_by,
            'notes'    => $dispensation->reason,
            'quarter_id' => $quarter?->id,
        ];
    }

    /**
     * Cek kelayakan massal — dipakai panitia ujian untuk daftar peserta triwulan.
     */
    public function checkBatch($students, ?AcademicQuarter $quarter = null): array
    {
        $results = [];
        foreach ($students as $student) {
            $results[] = $this->check($student, $quarter);
        }

        return [
            'summary' => [
                'total'        => count($results),
                'eligible'     => count(array_filter($results, fn($r) => $r['status'] === 'eligible')),
                'dispensation' => count(array_filter($results, fn($r) => $r['status'] === 'dispensation')),
                'blocked'      => count(array_filter($results, fn($r) => $r['status'] === 'blocked')),
                'unknown'      => count(array_filter($results, fn($r) => $r['status'] === 'unknown')),
            ],
            'students' => $results,
        ];
    }
}
