<?php

namespace App\Exports;

use App\Models\AcademicYear;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AcademicYearReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return AcademicYear::all();
    }

    /**
     * @param mixed $academicYear
     * @return array
     */
    public function map($academicYear): array
    {
        return [
            $academicYear->name,
            $academicYear->start_date ? $academicYear->start_date->format('d-m-Y') : '-',
            $academicYear->end_date ? $academicYear->end_date->format('d-m-Y') : '-',
            $academicYear->active ? 'Aktif' : 'Tidak Aktif',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tahun Ajaran',
            'Tanggal Mulai',
            'Tanggal Selesai',
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
