<?php

namespace App\Imports;

use App\Models\PositionAssignment;
use App\Models\Position;
use App\Models\Staff;
use App\Models\AcademicYear;
use App\Models\Hostel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Throwable;

class PositionAssignmentImport implements
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
        } catch (Throwable $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        try {
            $positionId = $this->cleanNumericString($row['position_id'] ?? null);
            $staffId = $this->cleanNumericString($row['staff_id'] ?? null);
            $academicYearId = $this->cleanNumericString($row['academic_year_id'] ?? null);
            $hostelId = $this->cleanNumericString($row['hostel_id'] ?? null);
            $startDate = $this->transformDate($row['start_date'] ?? null);
            $endDate = $this->transformDate($row['end_date'] ?? null);
            $letter = trim($row['assignment_letter'] ?? '');
            $notes = trim($row['notes'] ?? '');
            $isActive = $row['is_active'] ?? '1';

            if (!$positionId || !Position::find($positionId)) {
                $this->errors[] = "Row: Position ID {$positionId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            if (!$staffId || !Staff::find($staffId)) {
                $this->errors[] = "Row: Staff ID {$staffId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            if ($academicYearId && !AcademicYear::find($academicYearId)) {
                $this->errors[] = "Row: Academic Year ID {$academicYearId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            if ($hostelId && !Hostel::find($hostelId)) {
                $this->errors[] = "Row: Hostel ID {$hostelId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            if (!$startDate) {
                $this->errors[] = "Row: Start Date is missing or invalid - skipped";
                $this->failureCount++;
                return null;
            }

            $this->successCount++;

            return new PositionAssignment([
                'position_id' => $positionId,
                'staff_id' => $staffId,
                'academic_year_id' => $academicYearId ?: null,
                'hostel_id' => $hostelId ?: null,
                'start_date' => $startDate,
                'end_date' => $endDate ?: null,
                'assignment_letter' => $letter ?: null,
                'notes' => $notes ?: null,
                'is_active' => (bool)$isActive,
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
            'position_id' => 'required|exists:positions,id',
            'staff_id' => 'required|exists:staff,id',
            'start_date' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'position_id.required' => 'ID jabatan wajib diisi.',
            'staff_id.required' => 'ID staf wajib diisi.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Position Assignment Import error: ' . $e->getMessage());
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
