<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Student::with(['activeRoom'])->get();
    }

    /**
     * @var Student $student
     */
    public function map($student): array
    {
        // Get active room id
        $roomId = null;
        if ($student->activeRoom->isNotEmpty()) {
            $roomId = $student->activeRoom->first()->id;
        }

        return [
            $student->id,
            $student->nis,
            $student->nik,
            $student->kk,
            $student->first_name,
            $student->last_name,
            $student->gender,
            $student->born_in,
            $student->born_at ? $student->born_at->format('Y-m-d') : null,
            $student->address,
            $student->village_id,
            $student->village, // Text field
            $student->district,
            $student->postal_code,
            $student->phone,
            $student->last_education,
            $student->program_id,
            $student->hostel_id,
            $roomId, // Added room_id
            $student->status,
            $student->period,
            $student->parent_id,
            $student->photo,
            $student->created_at ? $student->created_at->format('Y-m-d H:i:s') : null,
            $student->updated_at ? $student->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    public function headings(): array
    {
        return [
            'id',
            'nis',
            'nik',
            'kk',
            'first_name',
            'last_name',
            'gender',
            'born_in',
            'born_at',
            'address',
            'village_id',
            'village',
            'district',
            'postal_code',
            'phone',
            'last_education',
            'program_id',
            'hostel_id',
            'room_id', // Added room_id heading
            'status',
            'period',
            'parent_id',
            'photo',
            'created_at',
            'updated_at',
        ];
    }
}
