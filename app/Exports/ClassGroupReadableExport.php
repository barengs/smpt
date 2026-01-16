<?php

namespace App\Exports;

use App\Models\ClassGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassGroupReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ClassGroup::with(['classroom', 'advisor', 'educational_institution'])->get();
    }

    /**
     * @param mixed $classGroup
     * @return array
     */
    public function map($classGroup): array
    {
        return [
            $classGroup->name,
            $classGroup->classroom ? $classGroup->classroom->name : '-',
            $classGroup->advisor ? $classGroup->advisor->full_name : '-',
            $classGroup->educational_institution ? $classGroup->educational_institution->name : '-',
            $classGroup->status,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Rombel',
            'Kelas',
            'Wali Kelas',
            'Institusi Pendidikan',
            'Status',
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
