<?php

namespace App\Imports;

use App\Models\Registration;
use App\Models\ParentProfile;
use App\Models\User;
use App\Models\Program;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistrationsImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    protected $errors = [];
    protected $successCount = 0;
    protected $failureCount = 0;
    protected $skippedCount = 0;
    protected $warnings = [];
    private $lastNumberSequence = null;

    /**
     * Clean numeric string field from Excel
     */
    private function cleanNumericString($value): ?string
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return null;
        }

        $cleaned = (string) $value;
        $cleaned = ltrim($cleaned, "'");
        $cleaned = trim($cleaned);

        if (preg_match('/^[\d.]+E\+?\d+$/i', $cleaned)) {
            $cleaned = number_format((float) $cleaned, 0, '', '');
        }

        return $cleaned !== '' ? $cleaned : null;
    }

    /**
     * Transform a date value to a proper format
     */
    private function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($value - 2)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generateRegistNumber()
    {
        if ($this->lastNumberSequence === null) {
            $lastRegistration = Registration::orderBy('created_at', 'desc')->first();
            if (!$lastRegistration) {
                $this->lastNumberSequence = 0;
            } else {
                $this->lastNumberSequence = (int) substr($lastRegistration->registration_number, -3);
            }
        }
        
        $this->lastNumberSequence++;
        $nextNumber = str_pad($this->lastNumberSequence, 3, '0', STR_PAD_LEFT);
        return 'REG' . date('Y') . $nextNumber;
    }

    /**
     * @param \Illuminate\Support\Collection $rows
     */
    public function collection(\Illuminate\Support\Collection $rows)
    {
        $preparedRows = [];
        $nisList = [];
        $nikList = [];

        foreach ($rows as $index => $row) {
            $nis = $this->cleanNumericString($row['santri_nisn'] ?? '') ?? '';
            $nik = $this->cleanNumericString($row['santri_nik'] ?? null);
            
            $preparedRows[$index] = [
                'cleaned_nis' => $nis,
                'cleaned_nik' => $nik,
                'original_row' => $row,
            ];

            if ($nis) $nisList[] = $nis;
            if ($nik) $nikList[] = $nik;
        }

        // Batch query existing registrations
        $existingNis = [];
        if (!empty($nisList)) {
            $existingNis = Registration::whereIn('nis', $nisList)
                ->pluck('nis')
                ->map(fn($item) => (string)$item)
                ->flip()
                ->toArray();
        }

        $existingNik = [];
        if (!empty($nikList)) {
            $existingNik = Registration::whereIn('nik', $nikList)
                ->pluck('nik')
                ->map(fn($item) => (string)$item)
                ->flip()
                ->toArray();
        }

        $processedNis = [];
        $processedNik = [];

        foreach ($preparedRows as $data) {
            $row = $data['original_row'];
            $nis = $data['cleaned_nis'];
            $nik = $data['cleaned_nik'];
            
            try {
                $waliNik = $this->cleanNumericString($row['wali_nik'] ?? null);
                if (!$waliNik) {
                    $this->errors[] = "Baris dengan Nama Santri '" . ($row['santri_nama_depan'] ?? '-') . "' gagal: NIK Wali diperlukan.";
                    $this->failureCount++;
                    continue;
                }

                if (isset($existingNis[$nis]) || isset($processedNis[$nis])) {
                    $this->warnings[] = "Pendaftaran dengan NISN {$nis} sudah ada - dilewati";
                    $this->skippedCount++;
                    continue;
                }

                if ($nik && (isset($existingNik[$nik]) || isset($processedNik[$nik]))) {
                    $this->warnings[] = "Pendaftaran dengan NIK Santri {$nik} sudah ada - dilewati";
                    $this->skippedCount++;
                    continue;
                }

                $waliNamaDepan = $row['wali_nama_depan'] ?? null;
                if (!$waliNamaDepan) {
                    $this->errors[] = "Baris dengan NISN '{$nis}' gagal: Nama depan wali diperlukan.";
                    $this->failureCount++;
                    continue;
                }

                DB::beginTransaction();

                // 1. Create or Update Parent Profile
                $parent = ParentProfile::where('nik', $waliNik)->first();

                if (!$parent) {
                    $email = $row['wali_email'] ?? $waliNik;
                    $user = User::create([
                        'name' => $waliNamaDepan,
                        'email' => $email,
                        'password' => bcrypt('password'),
                    ]);

                    $parent = $user->parent()->create([
                        'first_name' => $waliNamaDepan,
                        'last_name' => $row['wali_nama_belakang'] ?? null,
                        'nik' => $waliNik,
                        'kk' => $this->cleanNumericString($row['wali_kk'] ?? null),
                        'phone' => $this->cleanNumericString($row['wali_telepon'] ?? null),
                        'email' => $row['wali_email'] ?? null,
                        'gender' => strtoupper($row['wali_jenis_kelamin'] ?? 'L'),
                        'parent_as' => strtolower($row['wali_sebagai'] ?? 'ayah'),
                        'card_address' => $row['wali_alamat_ktp'] ?? null,
                        'domicile_address' => $row['wali_alamat_domisili'] ?? null,
                        'occupation_id' => $this->cleanNumericString($row['wali_pekerjaan_id'] ?? null),
                        'education_id' => $this->cleanNumericString($row['wali_pendidikan_id'] ?? null),
                    ]);

                    if ($parent) {
                        $user->assignRole('orangtua');
                    }
                } else {
                    // Update existing parent profile
                    $updateData = [];
                    if (!empty($row['wali_nama_depan'])) $updateData['first_name'] = $row['wali_nama_depan'];
                    if (isset($row['wali_nama_belakang'])) $updateData['last_name'] = $row['wali_nama_belakang'];
                    if (isset($row['wali_kk'])) $updateData['kk'] = $this->cleanNumericString($row['wali_kk']);
                    if (isset($row['wali_telepon'])) $updateData['phone'] = $this->cleanNumericString($row['wali_telepon']);
                    if (isset($row['wali_email'])) $updateData['email'] = $row['wali_email'];
                    if (isset($row['wali_jenis_kelamin'])) $updateData['gender'] = strtoupper($row['wali_jenis_kelamin']);
                    if (isset($row['wali_sebagai'])) $updateData['parent_as'] = strtolower($row['wali_sebagai']);
                    if (isset($row['wali_alamat_ktp'])) $updateData['card_address'] = $row['wali_alamat_ktp'];
                    if (isset($row['wali_alamat_domisili'])) $updateData['domicile_address'] = $row['wali_alamat_domisili'];
                    if (isset($row['wali_pekerjaan_id'])) $updateData['occupation_id'] = $this->cleanNumericString($row['wali_pekerjaan_id']);
                    if (isset($row['wali_pendidikan_id'])) $updateData['education_id'] = $this->cleanNumericString($row['wali_pendidikan_id']);

                    if (!empty($updateData)) {
                        $parent->update($updateData);
                    }
                }

                // 2. Create Registration
                $registration = Registration::create([
                    'registration_number' => $this->generateRegistNumber(),
                    'registration_date' => now(),
                    'parent_id' => $waliNik,
                    'nis' => $nis,
                    'period' => $row['period'] ?? date('Y'),
                    'nik' => $nik,
                    'kk' => $this->cleanNumericString($row['wali_kk'] ?? '') ?? '',
                    'first_name' => $row['santri_nama_depan'],
                    'last_name' => $row['santri_nama_belakang'] ?? null,
                    'gender' => strtoupper($row['santri_jenis_kelamin'] ?? 'L'),
                    'address' => $row['santri_alamat'] ?? null,
                    'born_in' => $row['santri_tempat_lahir'] ?? null,
                    'born_at' => $this->transformDate($row['santri_tanggal_lahir'] ?? null),
                    'village_id' => $this->cleanNumericString($row['santri_desa_code'] ?? null),
                    'postal_code' => $this->cleanNumericString($row['santri_kode_pos'] ?? null),
                    'phone' => $this->cleanNumericString($row['santri_telepon'] ?? null),
                    'status' => $row['status'] ?? 'pending',
                    'program_id' => $this->cleanNumericString($row['program_id'] ?? null),
                    'previous_school' => $row['pendidikan_sekolah_asal'] ?? null,
                    'previous_school_address' => $row['pendidikan_alamat_sekolah'] ?? null,
                    'certificate_number' => $row['pendidikan_nomor_ijazah'] ?? null,
                    'education_level_id' => $this->cleanNumericString($row['pendidikan_jenjang_sebelumnya'] ?? null),
                    'previous_madrasah' => $row['madrasah_sekolah_asal'] ?? null,
                    'previous_madrasah_address' => $row['madrasah_alamat_sekolah'] ?? null,
                    'certificate_madrasah' => $row['madrasah_nomor_ijazah'] ?? null,
                    'madrasah_level_id' => $this->cleanNumericString($row['madrasah_jenjang_sebelumnya'] ?? null),
                    'payment_status' => 'pending',
                ]);

                DB::commit();

                if ($nis) $processedNis[$nis] = true;
                if ($nik) $processedNik[$nik] = true;

                $this->successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Error memproses baris (NISN: {$nis}): " . $e->getMessage();
                $this->failureCount++;
            }
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'wali_nik' => 'required|max:16',
            'wali_nama_depan' => 'required|string|max:255',
            'santri_nisn' => 'required|max:20',
            'santri_nama_depan' => 'required|string|max:255',
            'santri_jenis_kelamin' => 'required|in:L,P,l,p',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'wali_nik.required' => 'NIK Wali wajib diisi',
            'wali_nama_depan.required' => 'Nama depan wali wajib diisi',
            'santri_nisn.required' => 'NISN Santri wajib diisi',
            'santri_nama_depan.required' => 'Nama depan santri wajib diisi',
            'santri_jenis_kelamin.required' => 'Jenis kelamin santri wajib diisi (L/P)',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Registration Import error: ' . $e->getMessage());
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            $this->failureCount++;
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
