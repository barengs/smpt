<?php

namespace App\Exports;

use App\Models\EducationClass;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EducationClassReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return EducationClass::all();
    }

    /**
     * @param mixed $educationClass
     * @return array
     */
    public function map($educationClass): array
    {
        return [
            $educationClass->name,
            $educationClass->description ?? '-',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Kelompok Pendidikan',
            'Deskripsi',
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
