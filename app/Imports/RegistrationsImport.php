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
     * Prepare data for validation by mapping synonyms and cleaning strings
     */
    public function prepareForValidation($data, $index)
    {
        $mapped = [];
        $dataArray = is_array($data) ? $data : (method_exists($data, 'toArray') ? $data->toArray() : (array)$data);

        $mapping = [
            'wali_nik' => ['wali_nik', 'parent_id', 'nik_wali', 'no_nik_wali', 'nik_orang_tua', 'nik_orangtua'],
            'wali_nama_depan' => ['wali_nama_depan', 'nama_depan_wali', 'nama_wali', 'nama_lengkap_wali', 'nama_depan_orang_tua', 'nama_orangtua'],
            'wali_nama_belakang' => ['wali_nama_belakang', 'nama_belakang_wali', 'nama_belakang_orang_tua'],
            'wali_kk' => ['wali_kk', 'kk_wali', 'no_kk_wali', 'kartu_keluarga_wali'],
            'wali_telepon' => ['wali_telepon', 'telepon_wali', 'no_telp_wali', 'no_hp_wali', 'hp_wali', 'no_telepon_wali'],
            'wali_email' => ['wali_email', 'email_wali'],
            'wali_jenis_kelamin' => ['wali_jenis_kelamin', 'jk_wali', 'jenis_kelamin_wali'],
            'wali_sebagai' => ['wali_sebagai', 'hubungan_wali', 'status_wali'],
            'wali_alamat_ktp' => ['wali_alamat_ktp', 'alamat_ktp_wali', 'alamat_ktp_orangtua'],
            'wali_alamat_domisili' => ['wali_alamat_domisili', 'alamat_domisili_wali', 'alamat_domisili_orangtua'],
            'wali_pekerjaan_id' => ['wali_pekerjaan_id', 'pekerjaan_wali_id', 'pekerjaan_id_wali'],
            'wali_pendidikan_id' => ['wali_pendidikan_id', 'pendidikan_wali_id', 'pendidikan_id_wali'],
            'santri_nisn' => ['santri_nisn', 'nis', 'nisn_santri', 'nis_santri', 'nisn', 'no_induk_santri', 'no_induk'],
            'santri_nama_depan' => ['santri_nama_depan', 'first_name', 'nama_depan_santri', 'nama_santri', 'nama_lengkap_santri', 'nama_depan'],
            'santri_nama_belakang' => ['santri_nama_belakang', 'last_name', 'nama_belakang_santri', 'nama_belakang'],
            'santri_nik' => ['santri_nik', 'nik', 'nik_santri', 'no_nik_santri', 'nik_anak'],
            'santri_jenis_kelamin' => ['santri_jenis_kelamin', 'gender', 'jk_santri', 'jenis_kelamin_santri', 'jenis_kelamin', 'jk'],
            'santri_alamat' => ['santri_alamat', 'address', 'alamat_santri', 'alamat'],
            'santri_tempat_lahir' => ['santri_tempat_lahir', 'born_in', 'tempat_lahir_santri', 'tempat_lahir', 'tmp_lahir'],
            'santri_tanggal_lahir' => ['santri_tanggal_lahir', 'born_at', 'tanggal_lahir_santri', 'tanggal_lahir', 'tgl_lahir'],
            'santri_desa_code' => ['santri_desa_code', 'village_id', 'desa_code_santri', 'kode_desa_santri', 'kode_desa', 'village_code', 'desa_id'],
            'santri_telepon' => ['santri_telepon', 'phone', 'telepon_santri', 'no_telp_santri', 'no_hp_santri', 'hp_santri', 'no_telepon_santri', 'telepon'],
            'santri_kode_pos' => ['santri_kode_pos', 'postal_code', 'kode_pos_santri', 'kode_pos', 'kodepos'],
            'program_id' => ['program_id', 'id_program'],
            'period' => ['period', 'periode', 'tahun_ajaran'],
            'status' => ['status', 'status_pendaftaran']
        ];

        foreach ($mapping as $targetKey => $synonyms) {
            foreach ($synonyms as $synonym) {
                if (isset($dataArray[$synonym])) {
                    $mapped[$targetKey] = $dataArray[$synonym];
                    continue 2;
                }
                
                // Normalization check
                $normalizedSynonym = strtolower(str_replace([' ', '_', '.', '-'], '', $synonym));
                foreach ($dataArray as $rowKey => $rowVal) {
                    $normalizedRowKey = strtolower(str_replace([' ', '_', '.', '-'], '', $rowKey));
                    if ($normalizedRowKey === $normalizedSynonym) {
                        $mapped[$targetKey] = $rowVal;
                        continue 3;
                    }
                }
            }
        }
        
        // Retain any other fields not explicitly mapped
        foreach ($dataArray as $k => $v) {
            if (!isset($mapped[$k])) {
                $mapped[$k] = $v;
            }
        }

        // Clean NIK, KK, etc.
        if (isset($mapped['wali_nik'])) {
            $mapped['wali_nik'] = $this->cleanNumericString($mapped['wali_nik']);
        }
        if (isset($mapped['wali_kk'])) {
            $mapped['wali_kk'] = $this->cleanNumericString($mapped['wali_kk']);
        }
        if (isset($mapped['wali_telepon'])) {
            $mapped['wali_telepon'] = $this->cleanNumericString($mapped['wali_telepon']);
        }
        if (isset($mapped['santri_nisn'])) {
            $mapped['santri_nisn'] = $this->cleanNumericString($mapped['santri_nisn']);
        }
        if (isset($mapped['santri_nik'])) {
            $mapped['santri_nik'] = $this->cleanNumericString($mapped['santri_nik']);
        }
        if (isset($mapped['santri_telepon'])) {
            $mapped['santri_telepon'] = $this->cleanNumericString($mapped['santri_telepon']);
        }
        if (isset($mapped['santri_kode_pos'])) {
            $mapped['santri_kode_pos'] = $this->cleanNumericString($mapped['santri_kode_pos']);
        }
        if (isset($mapped['santri_desa_code'])) {
            $mapped['santri_desa_code'] = $this->cleanNumericString($mapped['santri_desa_code']);
        }

        // Auto-resolve parent name/details if missing but NIK is available
        if (empty($mapped['wali_nama_depan'])) {
            $parentNik = $mapped['wali_nik'] ?? null;
            $resolvedName = null;
            if ($parentNik) {
                $parentProfile = DB::table('parent_profiles')->where('nik', $parentNik)->first();
                if ($parentProfile) {
                    $resolvedName = $parentProfile->first_name;
                    $mapped['wali_nama_belakang'] = $parentProfile->last_name;
                    $mapped['wali_kk'] = $parentProfile->kk;
                    $mapped['wali_telepon'] = $parentProfile->phone;
                    $mapped['wali_email'] = $parentProfile->email;
                    $mapped['wali_jenis_kelamin'] = $parentProfile->gender;
                    $mapped['wali_sebagai'] = $parentProfile->parent_as;
                    $mapped['wali_alamat_ktp'] = $parentProfile->card_address;
                    $mapped['wali_alamat_domisili'] = $parentProfile->domicile_address;
                    $mapped['wali_pekerjaan_id'] = $parentProfile->occupation_id;
                    $mapped['wali_pendidikan_id'] = $parentProfile->education_id;
                }
            }
            if (empty($resolvedName)) {
                $santriFirstName = $mapped['santri_nama_depan'] ?? null;
                $resolvedName = 'Wali ' . ($santriFirstName ?? 'Santri');
            }
            $mapped['wali_nama_depan'] = $resolvedName;
        }

        return $mapped;
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
            // Normalize row keys using prepareForValidation
            $normalizedRow = $this->prepareForValidation($row, $index);
            $nis = $this->cleanNumericString($normalizedRow['santri_nisn'] ?? '') ?? '';
            $nik = $this->cleanNumericString($normalizedRow['santri_nik'] ?? null);
            
            $preparedRows[$index] = [
                'cleaned_nis' => $nis,
                'cleaned_nik' => $nik,
                'original_row' => $normalizedRow,
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
