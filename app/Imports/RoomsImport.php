<?php

namespace App\Imports;

use App\Models\Room;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RoomsImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure
{
    private $errors = [];
    private $successCount = 0;
    private $failureCount = 0;

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                Room::create([
                    'hostel_id'   => $row['hostel_id'],
                    'name'        => $row['name'],
                    'capacity'    => $row['capacity'] ?? 0,
                    'description' => $row['description'] ?? null,
                    'is_active'   => $row['is_active'] ?? true,
                ]);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = "Error at room {$row['name']}: " . $e->getMessage();
                $this->failureCount++;
                Log::error("Room import error: " . $e->getMessage());
            }
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'hostel_id' => 'required|exists:hostels,id',
            'name'      => 'required|string|max:255',
            'capacity'  => 'required|integer|min:0',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
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
