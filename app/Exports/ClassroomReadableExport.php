<?php

namespace App\Exports;

use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassroomReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Classroom::with('school')->get();
    }

    /**
     * @param mixed $classroom
     * @return array
     */
    public function map($classroom): array
    {
        return [
            $classroom->name,
            $classroom->level,
            $classroom->school ? $classroom->school->name : '-',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Kelas',
            'Tingkat',
            'Institusi Pendidikan',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
