<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Program;
use App\Models\Hostel;
use App\Models\ParentProfile;
use Maatwebsite\Excel\Concerns\ToModel;
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

class StudentsImport implements
    ToModel,
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

    /**
     * Clean numeric string field from Excel
     *
     * Handles cases where:
     * - Excel stores numbers in scientific notation (e.g., 3.5280615E+15)
     * - User prefixes with apostrophe to prevent scientific notation (e.g., '3528061508860021)
     * - Value contains leading/trailing whitespace
     *
     * @param mixed $value
     * @return string|null
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
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Clean numeric string fields to handle:
            // - Excel scientific notation (3.5280615E+15)
            // - Leading apostrophe ('3528061508860021)
            // - Whitespace issues
            $nis = $this->cleanNumericString($row['nis'] ?? '') ?? '';
            $nik = $this->cleanNumericString($row['nik'] ?? null);
            $kk = $this->cleanNumericString($row['kk'] ?? null);
            $phone = $this->cleanNumericString($row['phone'] ?? null);
            $postalCode = $this->cleanNumericString($row['postal_code'] ?? null);
            $villageId = $this->cleanNumericString($row['village_id'] ?? null);
            $programId = $this->cleanNumericString($row['program_id'] ?? '') ?? '';
            $hostelId = $this->cleanNumericString($row['hostel_id'] ?? null);

            // Check if NIS already exists
            $existingStudent = Student::where('nis', $nis)->first();
            if ($existingStudent) {
                $this->errors[] = "NIS {$nis} already exists - skipped";
                $this->failureCount++;
                return null;
            }

            $this->successCount++;

            return new Student([
                'parent_id'       => $row['parent_id'] ?? null,
                'nis'             => $nis,
                'period'          => $row['period'] ?? null,
                'nik'             => $nik,
                'kk'              => $kk,
                'first_name'      => $row['first_name'],
                'last_name'       => $row['last_name'] ?? null,
                'gender'          => strtoupper($row['gender']),
                'address'         => $row['address'] ?? null,
                'born_in'         => $row['born_in'] ?? null,
                'born_at'         => $this->transformDate($row['born_at'] ?? null),
                'last_education'  => $row['last_education'] ?? null,
                'village_id'      => $villageId,
                'village'         => $row['village'] ?? null,
                'district'        => $row['district'] ?? null,
                'postal_code'     => $postalCode,
                'phone'           => $phone,
                'hostel_id'       => $hostelId,
                'program_id'      => $programId,
                'status'          => $row['status'] ?? 'Aktif',
                'photo'           => null,
                'user_id'         => Auth::id() ?? 1, // Use authenticated user ID, fallback to 1
            ]);
        } catch (\Exception $e) {
            $this->errors[] = "Error importing row: " . $e->getMessage();
            $this->failureCount++;
            return null;
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
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'nis.required' => 'NIS is required',
            'nis.unique' => 'NIS already exists',
            'first_name.required' => 'First name is required',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender must be L or P',
            'program_id.required' => 'Program ID is required',
            'program_id.exists' => 'Program ID does not exist',
            'hostel_id.exists' => 'Hostel ID does not exist',
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
}
