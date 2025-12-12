<?php

namespace App\Imports;

use App\Models\User;
use App\Models\ParentProfile;
use App\Models\Occupation;
use App\Models\Education;
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

class ParentsImport implements
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
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Convert numeric fields to string to handle Excel numeric values
            $nik = (string) ($row['nik'] ?? '');
            $kk = (string) ($row['kk'] ?? '');
            $phone = (string) ($row['phone'] ?? '');
            $occupationId = !empty($row['occupation_id']) ? (string) $row['occupation_id'] : null;
            $educationId = !empty($row['education_id']) ? (string) $row['education_id'] : null;

            // Check if NIK already exists
            $existingParent = ParentProfile::where('nik', $nik)->first();
            if ($existingParent) {
                $this->errors[] = "NIK {$nik} already exists - skipped";
                $this->failureCount++;
                return null;
            }

            // Check if KK already exists
            $existingKK = ParentProfile::where('kk', $kk)->first();
            if ($existingKK) {
                $this->errors[] = "KK {$kk} already exists - skipped";
                $this->failureCount++;
                return null;
            }

            DB::beginTransaction();

            try {
                // Create user account with NIK as email and default password
                $user = User::create([
                    'name' => $row['first_name'] . ' ' . ($row['last_name'] ?? ''),
                    'email' => $nik, // Use NIK as email
                    'password' => Hash::make('password'), // Default password
                ]);

                // Assign user role
                $user->syncRoles('orangtua');

                // Create parent profile
                $parent = new ParentProfile([
                    'user_id'           => $user->id,
                    'first_name'        => $row['first_name'],
                    'last_name'         => $row['last_name'] ?? null,
                    'nik'               => $nik,
                    'kk'                => $kk,
                    'gender'            => strtoupper($row['gender']),
                    'parent_as'         => strtolower($row['parent_as'] ?? 'ayah'),
                    'card_address'      => $row['card_address'] ?? null,
                    'domicile_address'  => $row['domicile_address'] ?? null,
                    'phone'             => $phone,
                    'email'             => $row['email'] ?? null,
                    'occupation_id'     => $occupationId,
                    'education_id'      => $educationId,
                    'photo'             => null,
                ]);

                $parent->save();

                DB::commit();

                $this->successCount++;
                return $parent;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Error creating user/parent for NIK {$nik}: " . $e->getMessage();
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
            'nik' => 'required|max:16',
            'kk' => 'required|max:16',
            'first_name' => 'required|string|max:255',
            'gender' => 'required|in:L,P,l,p',
            'parent_as' => 'required|in:ayah,ibu,Ayah,Ibu',
            'last_name' => 'nullable|string|max:255',
            'card_address' => 'nullable|string|max:255',
            'domicile_address' => 'nullable|string|max:255',
            'phone' => 'nullable|max:15',
            'email' => 'nullable|email|max:255',
            'occupation_id' => 'nullable',
            'education_id' => 'nullable',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'nik.required' => 'NIK is required',
            'nik.unique' => 'NIK already exists',
            'kk.required' => 'KK (Family Card) is required',
            'first_name.required' => 'First name is required',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender must be L or P',
            'parent_as.required' => 'Parent type is required',
            'parent_as.in' => 'Parent type must be ayah or ibu',
            'occupation_id.exists' => 'Occupation ID does not exist',
            'education_id.exists' => 'Education ID does not exist',
        ];
    }

    /**
     * Handle errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Parent import error: ' . $e->getMessage());
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
