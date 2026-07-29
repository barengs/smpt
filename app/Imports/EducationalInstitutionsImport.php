<?php

namespace App\Imports;

use App\Models\EducationalInstitution;
use App\Models\Education;
use App\Models\EducationClass;
use App\Models\Staff;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;

class EducationalInstitutionsImport implements
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

    public function model(array $row)
    {
        try {
            $name = trim($row['institution_name'] ?? '');
            $educationId = $this->cleanNumericString($row['education_id'] ?? null);
            $educationClassId = $this->cleanNumericString($row['education_class_id'] ?? null);
            $headmasterId = $this->cleanNumericString($row['headmaster_id'] ?? null);
            $regNumber = $this->cleanNumericString($row['registration_number'] ?? null);
            $address = trim($row['institution_address'] ?? '');
            $phone = $this->cleanNumericString($row['institution_phone'] ?? null);
            $email = trim($row['institution_email'] ?? '');
            $website = trim($row['institution_website'] ?? '');
            $status = trim($row['institution_status'] ?? 'active');
            $description = trim($row['institution_description'] ?? '');

            if (!$educationId || !Education::find($educationId)) {
                $this->errors[] = "Row '{$name}': Education ID {$educationId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            if (!$educationClassId || !EducationClass::find($educationClassId)) {
                $this->errors[] = "Row '{$name}': Education Class ID {$educationClassId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            if (!$headmasterId || !Staff::find($headmasterId)) {
                $this->errors[] = "Row '{$name}': Headmaster ID {$headmasterId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            if ($regNumber && EducationalInstitution::where('registration_number', $regNumber)->exists()) {
                $this->errors[] = "Row '{$name}': Registration Number {$regNumber} already exists - skipped";
                $this->failureCount++;
                return null;
            }

            $this->successCount++;

            return new EducationalInstitution([
                'education_id' => $educationId,
                'education_class_id' => $educationClassId,
                'registration_number' => $regNumber,
                'institution_name' => $name,
                'institution_address' => $address ?: null,
                'institution_phone' => $phone ?: null,
                'institution_email' => $email ?: null,
                'institution_website' => $website ?: null,
                'institution_status' => in_array($status, ['active', 'inactive']) ? $status : 'active',
                'institution_description' => $description ?: $name,
                'headmaster_id' => $headmasterId,
            ]);
        } catch (\Exception $e) {
            $this->errors[] = "Error importing row: " . $e->getMessage();
            $this->failureCount++;
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'institution_name' => 'required|string|max:255',
            'education_id' => 'required|exists:educations,id',
            'education_class_id' => 'required|exists:education_classes,id',
            'headmaster_id' => 'required|exists:staff,id',
            'institution_description' => 'required|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'institution_name.required' => 'Nama institusi wajib diisi.',
            'education_id.required' => 'ID pendidikan wajib diisi.',
            'education_class_id.required' => 'ID kelompok pendidikan wajib diisi.',
            'headmaster_id.required' => 'ID kepala sekolah wajib diisi.',
            'institution_description.required' => 'Deskripsi wajib diisi.',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Educational Institution Import error: ' . $e->getMessage());
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
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
}
