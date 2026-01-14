<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Student::with(['program', 'hostel'])->get();
    }

    /**
     * @var Student $student
     */
    public function map($student): array
    {
        return [
            $student->id,
            $student->nis,
            $student->nik,
            $student->kk,
            $student->first_name,
            $student->last_name,
            $student->gender,
            $student->born_in,
            $student->born_at,
            $student->address,
            $student->village,
            $student->district,
            $student->postal_code,
            $student->phone,
            $student->last_education,
            $student->program ? $student->program->name : $student->program_id,
            $student->hostel ? $student->hostel->name : $student->hostel_id,
            $student->status,
            $student->period,
            $student->parent_id, // Keeping ID for reference, could join parent name if needed but might be multiple columns
            $student->created_at,
            $student->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIS',
            'NIK',
            'NO KK',
            'First Name',
            'Last Name',
            'Gender',
            'Place of Birth',
            'Date of Birth',
            'Address',
            'Village',
            'District',
            'Postal Code',
            'Phone',
            'Last Education',
            'Program',
            'Hostel',
            'Status',
            'Period',
            'Parent ID',
            'Created At',
            'Updated At',
        ];
    }
}
