<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Program;
use App\Models\Hostel;
use App\Models\ParentProfile;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentsImport implements
    ToCollection,
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
     * @param \Illuminate\Support\Collection $rows
     */
    public function collection(\Illuminate\Support\Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Clean numeric string fields
                $nis = $this->cleanNumericString($row['nis'] ?? '') ?? '';
                $nik = $this->cleanNumericString($row['nik'] ?? null);
                $kk = $this->cleanNumericString($row['kk'] ?? null);
                $phone = $this->cleanNumericString($row['phone'] ?? null);
                $postalCode = $this->cleanNumericString($row['postal_code'] ?? null);
                $villageId = $this->cleanNumericString($row['village_id'] ?? null);
                $programId = $this->cleanNumericString($row['program_id'] ?? '') ?? '';
                $hostelId = $this->cleanNumericString($row['hostel_id'] ?? null);
                $roomId = $this->cleanNumericString($row['room_id'] ?? null);

                // Check if NIS already exists
                $existingStudent = Student::where('nis', $nis)->first();
                if ($existingStudent) {
                    $this->errors[] = "NIS {$nis} already exists - skipped";
                    $this->failureCount++;
                    continue;
                }

                DB::beginTransaction();

                $student = Student::create([
                    'parent_id'       => $row['parent_id'] ?? null,
                    'nis'             => $nis,
                    'period'          => $row['period'] ?? null,
                    'nik'             => $nik,
                    'kk'              => $kk,
                    'first_name'      => $row['first_name'],
                    'last_name'       => $row['last_name'] ?? null,
                    'gender'          => strtoupper($row['gender']),
                    'address'         => $row['address'] ?? null,
                    'born_in'         => $row['born_in'] ?? null,
                    'born_at'         => $this->transformDate($row['born_at'] ?? null),
                    'last_education'  => $row['last_education'] ?? null,
                    'village_id'      => $villageId,
                    'village'         => $row['village'] ?? null,
                    'district'        => $row['district'] ?? null,
                    'postal_code'     => $postalCode,
                    'phone'           => $phone,
                    'hostel_id'       => $hostelId,
                    'program_id'      => $programId,
                    'status'          => $row['status'] ?? 'Aktif',
                    'photo'           => null,
                    'user_id'         => Auth::id() ?? 1,
                ]);

                // Handle Room Assignment
                if ($roomId) {
                    $room = \App\Models\Room::find($roomId);
                    if ($room) {
                        // Insert into student_room_assignments
                        DB::table('student_room_assignments')->insert([
                            'student_id' => $student->id,
                            'room_id' => $room->id,
                            'academic_year_id' => null, // Or fetch current/active academic year if possible
                            'start_date' => now()->toDateString(),
                            'is_active' => true,
                            'notes' => 'Imported via Excel',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Update student hostel if room belongs to one
                        if ($room->hostel_id) {
                            $student->update(['hostel_id' => $room->hostel_id]);
                        }
                    } else {
                        // Log warning or error about room not found?
                        // For now we just skip assignment if room invalid
                    }
                }

                DB::commit();
                $this->successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Error processing NIS {$row['nis']}: " . $e->getMessage();
                $this->failureCount++;
            }
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'nis' => 'required|max:20',
            'first_name' => 'required|string|max:255',
            'gender' => 'required|in:L,P,l,p',
            'program_id' => 'required',
            'nik' => 'nullable|max:16',
            'status' => 'nullable|in:Tidak Aktif,Aktif,Tugas,Lulus,Dikeluarkan',
            'hostel_id' => 'nullable',
            'parent_id' => 'nullable',
            'room_id' => 'nullable|exists:rooms,id',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'nis.required' => 'NIS is required',
            'first_name.required' => 'First name is required',
            'gender.required' => 'Gender is required',
            'program_id.required' => 'Program ID is required',
            'room_id.exists' => 'Room ID does not exist',
        ];
    }

    /**
     * Handle errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Import error: ' . $e->getMessage());
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
        return 100;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100;
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
