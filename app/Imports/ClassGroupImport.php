<?php

namespace App\Imports;

use App\Models\ClassGroup;
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

class ClassGroupImport implements
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
            $classroomId = $this->cleanNumericString($row['classroom_id'] ?? null);
            $advisorId = $this->cleanNumericString($row['advisor_id'] ?? null);
            $educationalInstitutionId = $this->cleanNumericString($row['educational_institution_id'] ?? null);

            // Additional validation for Advisor Role if ID is provided
            if ($advisorId) {
                $staff = Staff::with('user')->find($advisorId);
                // If staff doesn't exist or doesn't have role 'walikelas', we could either skip or just log warning.
                // For now, let's treat it as an error to ensure data integrity, mirroring Controller logic.
                if (!$staff || !$staff->user || !$staff->user->hasRole('walikelas')) {
                    $this->errors[] = "Row with Name '{$name}': Advisor ID {$advisorId} is invalid or missing 'walikelas' role - skipped";
                    $this->failureCount++;
                    return null;
                }
            }

            $this->successCount++;

            return new ClassGroup([
                'name' => $name,
                'classroom_id' => $classroomId,
                'advisor_id' => $advisorId,
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
            'classroom_id' => 'required|exists:classrooms,id',
            'advisor_id' => 'nullable|exists:staff,id',
            'educational_institution_id' => 'nullable|exists:educational_institutions,id',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Name is required',
            'classroom_id.required' => 'Classroom ID is required',
            'classroom_id.exists' => 'Classroom ID does not exist',
            'advisor_id.exists' => 'Advisor (Staff) ID does not exist',
            'educational_institution_id.exists' => 'Educational Institution ID does not exist',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('ClassGroup Import error: ' . $e->getMessage());
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
