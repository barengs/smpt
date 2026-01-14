<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentReadableExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Student::with(['program', 'hostel', 'parents', 'village'])->get();
    }

    /**
     * @var Student $student
     */
    public function map($student): array
    {
        return [
            $student->nis,
            $student->first_name . ' ' . $student->last_name,
            $student->gender == 'L' ? 'Laki-laki' : 'Perempuan',
            $student->born_in . ', ' . ($student->born_at ? $student->born_at->format('d-m-Y') : '-'),
            $student->program ? $student->program->name : '-',
            $student->hostel ? $student->hostel->name : '-',
            $student->status,
            $student->address . ', ' . ($student->village ? $student->village : '-'),
            $student->parents ? $student->parents->father_name : '-',
            $student->phone,
        ];
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Lengkap',
            'Jenis Kelamin',
            'TTL',
            'Program Pendidikan',
            'Asrama',
            'Status',
            'Alamat',
            'Nama Ayah/Wali',
            'No. Telepon',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
