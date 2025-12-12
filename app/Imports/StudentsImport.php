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
            // Convert numeric fields to string to handle Excel numeric values
            $nis = (string) ($row['nis'] ?? '');
            $nik = !empty($row['nik']) ? (string) $row['nik'] : null;
            $kk = !empty($row['kk']) ? (string) $row['kk'] : null;
            $phone = !empty($row['phone']) ? (string) $row['phone'] : null;
            $postalCode = !empty($row['postal_code']) ? (string) $row['postal_code'] : null;
            $villageId = !empty($row['village_id']) ? (string) $row['village_id'] : null;
            $programId = (string) ($row['program_id'] ?? '');
            $hostelId = !empty($row['hostel_id']) ? (string) $row['hostel_id'] : null;

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
