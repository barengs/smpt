<?php

namespace App\Imports;

use App\Models\Position;
use App\Models\Organization;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;

class PositionImport implements
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
            $name = trim($row['name'] ?? '');
            $code = trim($row['code'] ?? '');
            $description = trim($row['description'] ?? '');
            $orgId = $this->cleanNumericString($row['organization_id'] ?? null);
            $parentId = $this->cleanNumericString($row['parent_id'] ?? null);
            $level = $this->cleanNumericString($row['level'] ?? '1');
            $isActive = $row['is_active'] ?? '1';

            if (!$orgId || !Organization::find($orgId)) {
                $this->errors[] = "Row '{$name}': Organization ID {$orgId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            if ($parentId && !Position::find($parentId)) {
                $this->errors[] = "Row '{$name}': Parent Position ID {$parentId} does not exist - skipped";
                $this->failureCount++;
                return null;
            }

            // Check unique code
            if ($code && Position::where('code', $code)->exists()) {
                $this->errors[] = "Row '{$name}': Code {$code} already exists - skipped";
                $this->failureCount++;
                return null;
            }

            $this->successCount++;

            return new Position([
                'name' => $name,
                'code' => $code,
                'description' => $description ?: null,
                'organization_id' => $orgId,
                'parent_id' => $parentId ?: null,
                'level' => $level ?: 1,
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'organization_id' => 'required|exists:organizations,id',
            'level' => 'required|integer|min:1',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Nama jabatan wajib diisi.',
            'code.required' => 'Kode jabatan wajib diisi.',
            'organization_id.required' => 'ID organisasi wajib diisi.',
            'level.required' => 'Level wajib diisi dengan angka.',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Position Import error: ' . $e->getMessage());
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
