<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudyTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    public function array(): array
    {
        return [
            [
                'Matematika',
                'Mata pelajaran Matematika untuk kelas 7', // description
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'description',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // name
            'B' => 50, // description
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
