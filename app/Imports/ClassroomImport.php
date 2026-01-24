<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\EducationalInstitution;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;

class ClassroomImport implements
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
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            $name = trim($row['name'] ?? '');
            $level = trim($row['level'] ?? '');
            $educationalInstitutionId = $this->cleanNumericString($row['educational_institution_id'] ?? null);

            // Basic validation for existence of institution
            if ($educationalInstitutionId) {
                if (!EducationalInstitution::find($educationalInstitutionId)) {
                    $this->errors[] = "Row with Name '{$name}': Educational Institution ID {$educationalInstitutionId} does not exist - skipped";
                    $this->failureCount++;
                    return null;
                }
            } else {
                 $this->errors[] = "Row with Name '{$name}': Educational Institution ID is missing - skipped";
                 $this->failureCount++;
                 return null;
            }

            $this->successCount++;

            return new Classroom([
                'name' => $name,
                'level' => $level,
                'educational_institution_id' => $educationalInstitutionId,
            ]);
        } catch (\Exception $e) {
            $this->errors[] = "Error importing row '{$name}': " . $e->getMessage();
            $this->failureCount++;
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'educational_institution_id' => 'required|exists:educational_institutions,id',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Name is required',
            'level.required' => 'Level is required',
            'educational_institution_id.required' => 'Educational Institution ID is required',
            'educational_institution_id.exists' => 'Educational Institution ID does not exist',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Classroom Import error: ' . $e->getMessage());
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
