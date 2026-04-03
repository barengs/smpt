<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Staff;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Row;

class StaffsImport implements
    OnEachRow,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure
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

    /**
     * Transform gender to L or P
     */
    private function transformGender($value): string
    {
        if (empty($value)) {
            return 'L';
        }

        $value = trim($value);
        $lower = strtolower($value);

        if ($lower === 'laki-laki' || $lower === 'l' || $lower === 'male' || $lower === 'man' || str_contains($lower, 'laki')) {
            return 'L';
        }

        if ($lower === 'perempuan' || $lower === 'p' || $lower === 'female' || $lower === 'woman' || str_contains($lower, 'perempuan')) {
            return 'P';
        }

        return 'L';
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
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $arrayRow = $row->toArray();

        try {
            // Clean numeric string fields
            $nik = $this->cleanNumericString($arrayRow['nik'] ?? null);
            $nip = $this->cleanNumericString($arrayRow['nip'] ?? null);
            $phone = $this->cleanNumericString($arrayRow['phone'] ?? null);
            $zipCode = $this->cleanNumericString($arrayRow['zip_code'] ?? null);
            $villageId = $this->cleanNumericString($arrayRow['village_id'] ?? null);
            $jobId = $this->cleanNumericString($arrayRow['job_id'] ?? null);

            $email = trim($arrayRow['email'] ?? '');
            $role = trim($arrayRow['role'] ?? 'staf');

            // Skip if email already exists
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                // If it exists, check if it's already a staff
                $existingStaff = Staff::where('user_id', $existingUser->id)->first();
                if ($existingStaff) {
                    $this->errors[] = "Email {$email} already exists as staff - skipped";
                    $this->failureCount++;
                    return;
                }
                
                // If user exists but not staff, just link to it later (but usually we expect new users)
                $user = $existingUser;
            } else {
                // Create user
                $user = User::create([
                    'name' => trim($arrayRow['first_name']) . ' ' . trim($arrayRow['last_name'] ?? ''),
                    'email' => $email,
                    'password' => Hash::make('password'),
                ]);
                $user->syncRoles($role);
            }

            // Create staff profile
            DB::beginTransaction();
            try {
                Staff::create([
                    'user_id' => $user->id,
                    'code' => $this->generateCode(),
                    'first_name' => trim($arrayRow['first_name']),
                    'last_name' => trim($arrayRow['last_name'] ?? ''),
                    'nik' => $nik,
                    'nip' => $nip,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $arrayRow['address'] ?? null,
                    'zip_code' => $zipCode,
                    'village_id' => $villageId,
                    'job_id' => $jobId,
                    'birth_place' => $arrayRow['birth_place'] ?? null,
                    'birth_date' => $this->transformDate($arrayRow['birth_date'] ?? null),
                    'gender' => $this->transformGender($arrayRow['gender'] ?? 'L'),
                    'marital_status' => $arrayRow['marital_status'] ?? 'Belum Menikah',
                    'status' => $arrayRow['status'] ?? 'Aktif',
                ]);
                DB::commit();
                $this->successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Error for email {$email}: " . $e->getMessage();
                $this->failureCount++;
            }

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->failureCount++;
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
            'gender' => 'required|string|max:20', // Broaden validation
            'last_name' => 'nullable|string|max:255',
            'nik' => 'nullable|max:16',
            'nip' => 'nullable|max:20',
            'phone' => 'nullable|max:20',
            'address' => 'nullable|string|max:500',
            'zip_code' => 'nullable|max:10',
            'birth_place' => 'nullable|string|max:100',
            'marital_status' => 'nullable|max:50',
            'status' => 'nullable|max:50',
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
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Staff import error: ' . $e->getMessage());
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            $this->failureCount++;
        }
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
}
