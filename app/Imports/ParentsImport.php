<?php

namespace App\Imports;

use App\Models\User;
use Spatie\Permission\Models\Role;
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
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
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
    WithChunkReading,
    SkipsEmptyRows,
    WithCustomCsvSettings
{
    protected $errors = [];
    protected $successCount = 0;
    protected $failureCount = 0;
    protected $role;

    public function __construct()
    {
        // Cache the role to avoid querying it for every row
        $this->role = Role::where('name', 'orangtua')->where('guard_name', 'api')->first();
        
        if (!$this->role) {
            $this->role = Role::create(['name' => 'orangtua', 'guard_name' => 'api']);
        }
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
        ];
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
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    /**
     * Prepare data for validation
     *
     * @param array $data
     * @param int $index
     * @return array
     */
    public function prepareForValidation($data, $index)
    {
        // Clean NIK
        if (isset($data['nik'])) {
            $data['nik'] = $this->cleanNumericString($data['nik']);
        }

        // Clean KK
        if (isset($data['kk'])) {
            $data['kk'] = $this->cleanNumericString($data['kk']);
        }

        // Clean Phone
        if (isset($data['phone'])) {
            $data['phone'] = $this->cleanNumericString($data['phone']);
        }
        
        // Clean Occupation & Education IDs
        if (isset($data['occupation_id'])) {
            $data['occupation_id'] = $this->cleanNumericString($data['occupation_id']);
        }
        if (isset($data['education_id'])) {
            $data['education_id'] = $this->cleanNumericString($data['education_id']);
        }

        return $data;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // However, model() receives the original row or the validated/prepared row?
            // In Maatwebsite Excel, model() receives the mapped row.
            // Since we don't use WithMapping explicitly for transformation beyond prepareForValidation, 
            // we should trust prepareForValidation modified it OR re-clean to be safe if functionality differs by version.
            // But typically prepareForValidation modifies the data passed to validation, and that data is passed to model()?
            // Actually, for ToModel, it passes the row from the file/collection. 
            // Safe bet: Re-apply cleaning or ensure we use the cleaned values.
            // Let's re-clean to be 100% sure we are using the generic cleaner we have.
            
            $nik = $row['nik']; // Should be cleaned if prepareForValidation works, but let's be safe.
            $kk = $row['kk'];
            
            // Re-ensure cleaning just in case prepareForValidation only affects validation layer
            $nik = $this->cleanNumericString($nik) ?? '';
            $kk = $this->cleanNumericString($kk) ?? '';
            $phone = $this->cleanNumericString($row['phone'] ?? null);
            $occupationId = $this->cleanNumericString($row['occupation_id'] ?? null);
            $educationId = $this->cleanNumericString($row['education_id'] ?? null);

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
                // Email logic: Use provided email or fallback to NIK
                $email = !empty($row['email']) ? $row['email'] : $nik;

                // Create user account
                $user = User::create([
                    'name' => $row['first_name'] . ' ' . ($row['last_name'] ?? ''),
                    'email' => $email,
                    'password' => bcrypt($nik, ['rounds' => 4]), // Optimize: Low cost for import speed
                ]);

                // Match Controller logic, using cached role
                if ($this->role) {
                    $user->assignRole($this->role);
                } else {
                     // Fallback just in case, though constructor should have handled it
                    $user->assignRole('orangtua');
                }

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
                    'email'             => $row['email'] ?? null, // Contact email
                    'occupation_id'     => $occupationId,
                    'education_id'      => $educationId,
                    'photo'             => null,
                ]);

                $parent->save();

                DB::commit();

                $this->successCount++;
                return null; // Return null to prevent Maatwebsite from trying to save again (avoiding duplicate ID error)

            } catch (\Throwable $e) {
                DB::rollBack();
                $this->errors[] = "Error creating user/parent for NIK {$nik}: " . $e->getMessage();
                $this->failureCount++;
                return null;
            }

        } catch (\Throwable $e) {
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
            'email' => 'nullable|string|max:255',
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
