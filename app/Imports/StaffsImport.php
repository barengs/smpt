<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Staff;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StaffsImport implements
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
    protected $lastStaffId = null;

    public function __construct()
    {
        // Get the last staff ID for code generation
        $lastStaff = Staff::orderBy('id', 'desc')->first();
        $this->lastStaffId = $lastStaff ? $lastStaff->id : 0;
    }

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
     * Generate staff code
     */
    private function generateCode(): string
    {
        $this->lastStaffId++;
        return 'SP' . str_pad($this->lastStaffId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Clean numeric string fields
            $nik = $this->cleanNumericString($row['nik'] ?? null);
            $nip = $this->cleanNumericString($row['nip'] ?? null);
            $phone = $this->cleanNumericString($row['phone'] ?? null);
            $zipCode = $this->cleanNumericString($row['zip_code'] ?? null);
            $villageId = $this->cleanNumericString($row['village_id'] ?? null);
            $jobId = $this->cleanNumericString($row['job_id'] ?? null);

            // Get email - required for user account
            $email = trim($row['email'] ?? '');

            // Get role - default to 'staf' if not provided
            $role = trim($row['role'] ?? 'staf');

            // Check if email already exists in users table
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                $this->errors[] = "Email {$email} already exists in users - skipped";
                $this->failureCount++;
                return null;
            }

            // Check if NIK already exists in staff table
            if ($nik) {
                $existingStaffNik = Staff::where('nik', $nik)->first();
                if ($existingStaffNik) {
                    $this->errors[] = "NIK {$nik} already exists - skipped";
                    $this->failureCount++;
                    return null;
                }
            }

            // Check if NIP already exists in staff table
            if ($nip) {
                $existingStaffNip = Staff::where('nip', $nip)->first();
                if ($existingStaffNip) {
                    $this->errors[] = "NIP {$nip} already exists - skipped";
                    $this->failureCount++;
                    return null;
                }
            }

            DB::beginTransaction();

            try {
                // Create user account with email and default password
                $user = User::create([
                    'name' => trim($row['first_name']) . ' ' . trim($row['last_name'] ?? ''),
                    'email' => $email,
                    'password' => Hash::make('password'), // Default password
                ]);

                // Assign role to user (from Excel or default to 'staf')
                $user->syncRoles($role);

                // Create staff profile
                $staff = new Staff([
                    'user_id' => $user->id,
                    'code' => $this->generateCode(),
                    'first_name' => trim($row['first_name']),
                    'last_name' => trim($row['last_name'] ?? ''),
                    'nik' => $nik,
                    'nip' => $nip,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $row['address'] ?? null,
                    'zip_code' => $zipCode,
                    'village_id' => $villageId,
                    'job_id' => $jobId,
                    'birth_place' => $row['birth_place'] ?? null,
                    'birth_date' => $this->transformDate($row['birth_date'] ?? null),
                    'gender' => strtoupper($row['gender'] ?? 'L'),
                    'marital_status' => $row['marital_status'] ?? 'Belum Menikah',
                    'status' => $row['status'] ?? 'Aktif',
                    'photo' => null,
                ]);

                $staff->save();

                DB::commit();

                $this->successCount++;
                return $staff;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Error creating user/staff for email {$email}: " . $e->getMessage();
                $this->failureCount++;
                return null;
            }

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
            'email' => 'required|email|max:255',
            'first_name' => 'required|string|max:255',
            'gender' => 'required|in:L,P,l,p',
            'last_name' => 'nullable|string|max:255',
            'nik' => 'nullable|max:16',
            'nip' => 'nullable|max:20',
            'phone' => 'nullable|max:20',
            'address' => 'nullable|string|max:500',
            'zip_code' => 'nullable|max:10',
            'birth_place' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:Belum Menikah,Menikah,Cerai,Duda/Janda',
            'status' => 'nullable|in:Aktif,Tidak Aktif',
            'role' => 'nullable|string|max:50',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email format is invalid',
            'first_name.required' => 'First name is required',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender must be L or P',
            'marital_status.in' => 'Marital status must be: Belum Menikah, Menikah, Cerai, or Duda/Janda',
            'status.in' => 'Status must be: Aktif or Tidak Aktif',
        ];
    }

    /**
     * Handle errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Staff import error: ' . $e->getMessage());
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
        return 50;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 50;
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
