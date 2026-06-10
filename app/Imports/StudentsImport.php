<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Program;
use App\Models\Hostel;
use App\Models\ParentProfile;
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

class StudentsImport implements
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

    /**
     * Clean numeric string field from Excel
     */
    private function cleanNumericString($value): ?string
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return null;
        }

        // Convert to string first
        $cleaned = (string) $value;

        // Remove leading apostrophe/single quote (Excel text marker)
        $cleaned = ltrim($cleaned, "'");

        // Remove leading/trailing whitespace
        $cleaned = trim($cleaned);

        // Handle scientific notation (e.g., 3.5280615E+15)
        if (preg_match('/^[\d.]+E\+?\d+$/i', $cleaned)) {
            // Convert scientific notation to full number string
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
            // Try to parse various date formats
            if (is_numeric($value)) {
                // Excel date serial number
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($value - 2)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get a value from a row supporting multiple synonym keys
     */
    private function getValueByKeys($row, array $keys, $default = null)
    {
        $rowArray = is_array($row) ? $row : (method_exists($row, 'toArray') ? $row->toArray() : (array)$row);
        foreach ($keys as $key) {
            if (isset($rowArray[$key])) {
                return $rowArray[$key];
            }
            $normalizedKey = strtolower(str_replace([' ', '_', '.', '-'], '', $key));
            foreach ($rowArray as $rowKey => $rowVal) {
                $normalizedRowKey = strtolower(str_replace([' ', '_', '.', '-'], '', $rowKey));
                if ($normalizedRowKey === $normalizedKey) {
                    return $rowVal;
                }
            }
        }
        return $default;
    }

    /**
     * @param \Illuminate\Support\Collection $rows
     */
    public function collection(\Illuminate\Support\Collection $rows)
    {
        // 1. Prepare data and collect IDs for batch checking
        $preparedRows = [];
        $nisList = [];
        $nikList = [];

        foreach ($rows as $index => $row) {
            // Clean fields
            $nis = $this->cleanNumericString($this->getValueByKeys($row, ['nis', 'no_induk', 'nomor_induk']) ?? '') ?? '';
            $nik = $this->cleanNumericString($this->getValueByKeys($row, ['nik', 'no_nik', 'nomor_nik']));
            
            // Store prepared data to avoid re-cleaning
            $preparedRows[$index] = [
                'cleaned_nis' => $nis,
                'cleaned_nik' => $nik,
                'original_row' => $row,
            ];

            if ($nis) $nisList[] = $nis;
            if ($nik) $nikList[] = $nik;
        }

        // 2. Batch query existing records
        $existingNis = [];
        if (!empty($nisList)) {
            $existingNis = Student::whereIn('nis', $nisList)
                ->pluck('nis')
                ->map(fn($item) => (string)$item)
                ->flip()
                ->toArray();
        }

        $existingNik = [];
        if (!empty($nikList)) {
            $existingNik = Student::whereIn('nik', $nikList)
                ->pluck('nik')
                ->map(fn($item) => (string)$item)
                ->flip()
                ->toArray();
        }

        // track duplicates within the current chunk to prevent double insertion
        $processedNis = [];
        $processedNik = [];

        // 3. Process each row
        foreach ($preparedRows as $data) {
            $row = $data['original_row'];
            $nis = $data['cleaned_nis'];
            $nik = $data['cleaned_nik'];
            
            try {
                $kk = $this->cleanNumericString($this->getValueByKeys($row, ['kk', 'no_kk', 'nomor_kk']));
                $phone = $this->cleanNumericString($this->getValueByKeys($row, ['phone', 'no_phone', 'no_telp', 'no_telepon', 'telepon', 'no_hp', 'hp']));
                $postalCode = $this->cleanNumericString($this->getValueByKeys($row, ['postal_code', 'kode_pos', 'kodepos', 'zip_code', 'zipcode']));
                $villageId = $this->cleanNumericString($this->getValueByKeys($row, ['village_id', 'santri_desa_code']));
                $programId = $this->cleanNumericString($this->getValueByKeys($row, ['program_id']) ?? '') ?? '';
                $hostelId = $this->cleanNumericString($this->getValueByKeys($row, ['hostel_id']));
                $roomId = $this->cleanNumericString($this->getValueByKeys($row, ['room_id']));
                $parentId = $this->cleanNumericString($this->getValueByKeys($row, ['parent_id']));

                // Check constraints
                if (isset($existingNis[$nis]) || isset($processedNis[$nis])) {
                    $this->warnings[] = "NIS {$nis} already exists - skipped";
                    $this->skippedCount++;
                    continue;
                }

                if ($nik && (isset($existingNik[$nik]) || isset($processedNik[$nik]))) {
                    $this->warnings[] = "NIK {$nik} already exists - skipped";
                    $this->skippedCount++;
                    continue;
                }

                // KK Duplicates are ALLOWED (No check performed)

                // Resolve village name & district name from village_id if not provided
                $village = $this->getValueByKeys($row, ['village', 'desa', 'kelurahan', 'desa_kelurahan']);
                $district = $this->getValueByKeys($row, ['district', 'kecamatan']);
                
                if (empty($village) || empty($district)) {
                    if ($villageId) {
                        $villageRow = DB::table('indonesia_villages')
                            ->where('id', $villageId)
                            ->orWhere('code', $villageId)
                            ->first();
                        if ($villageRow) {
                            if (empty($village)) {
                                $village = $villageRow->name;
                            }
                            if (empty($district)) {
                                $districtRow = DB::table('indonesia_districts')
                                    ->where('code', $villageRow->district_code)
                                    ->first();
                                if ($districtRow) {
                                    $district = $districtRow->name;
                                }
                            }
                        }
                    }
                }

                DB::beginTransaction();

                $student = Student::create([
                    'parent_id'       => $parentId,
                    'nis'             => $nis,
                    'period'          => $this->getValueByKeys($row, ['period']) ?? null,
                    'nik'             => $nik,
                    'kk'              => $kk,
                    'first_name'      => $this->getValueByKeys($row, ['first_name', 'nama_depan', 'nama']),
                    'last_name'       => $this->getValueByKeys($row, ['last_name', 'nama_belakang']) ?? null,
                    'gender'          => strtoupper($this->getValueByKeys($row, ['gender', 'jenis_kelamin', 'jk'])),
                    'address'         => $this->getValueByKeys($row, ['address', 'alamat']) ?? null,
                    'born_in'         => $this->getValueByKeys($row, ['born_in', 'tempat_lahir', 'tmp_lahir']) ?? null,
                    'born_at'         => $this->transformDate($this->getValueByKeys($row, ['born_at', 'tanggal_lahir', 'tgl_lahir']) ?? null),
                    'last_education'  => $this->getValueByKeys($row, ['last_education', 'pendidikan_terakhir', 'pendidikan']) ?? null,
                    'village_id'      => $villageId,
                    'village'         => $village,
                    'district'        => $district,
                    'postal_code'     => $postalCode,
                    'phone'           => $phone,
                    'hostel_id'       => $hostelId,
                    'program_id'      => $programId,
                    'status'          => $this->getValueByKeys($row, ['status']) ?? 'Aktif',
                    'photo'           => null,
                    'user_id'         => Auth::id() ?? 1,
                ]);

                // Handle Room Assignment
                if ($roomId) {
                    $room = \App\Models\Room::find($roomId);
                    if ($room) {
                        // Insert into student_room_assignments
                        DB::table('student_room_assignments')->insert([
                            'student_id' => $student->id,
                            'room_id' => $room->id,
                            'academic_year_id' => null, // Or fetch current/active academic year if possible
                            'start_date' => now()->toDateString(),
                            'is_active' => true,
                            'notes' => 'Imported via Excel',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Update student hostel if room belongs to one
                        if ($room->hostel_id) {
                            $student->update(['hostel_id' => $room->hostel_id]);
                        }
                    }
                }

                DB::commit();
                
                // Mark as processed
                if ($nis) $processedNis[$nis] = true;
                if ($nik) $processedNik[$nik] = true;
                
                $this->successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Error processing NIS {$row['nis']}: " . $e->getMessage();
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
            'nis' => 'required|max:20',
            'first_name' => 'required|string|max:255',
            'gender' => 'required|in:L,P,l,p',
            'program_id' => 'required',
            'nik' => 'nullable|max:16',
            'status' => 'nullable|in:Tidak Aktif,Aktif,Tugas,Lulus,Dikeluarkan',
            'hostel_id' => 'nullable',
            'parent_id' => 'nullable',
            'room_id' => 'nullable|exists:rooms,id',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'nis.required' => 'NIS is required',
            'first_name.required' => 'First name is required',
            'gender.required' => 'Gender is required',
            'program_id.required' => 'Program ID is required',
            'room_id.exists' => 'Room ID does not exist',
        ];
    }

    /**
     * Handle errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Import error: ' . $e->getMessage());
    }

    /**
     * Handle validation failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            $this->failureCount++;
        }
    }

    /**
     * Batch size for insertion
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get import errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get success count
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get failure count
     */
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
