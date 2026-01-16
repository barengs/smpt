<?php

namespace App\Exports;

use App\Models\Education;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EducationReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Education::all();
    }

    /**
     * @param mixed $education
     * @return array
     */
    public function map($education): array
    {
        return [
            $education->name,
            $education->level,
            $education->description ?? '-',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Jenjang Pendidikan',
            'Tingkat',
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
