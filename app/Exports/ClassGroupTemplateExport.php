<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassGroupTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    public function array(): array
    {
        return [
            [
                'X-A',
                '1', // classroom_id
                '10', // advisor_id (optional)
                '1', // educational_institution_id (optional)
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'classroom_id',
            'advisor_id',
            'educational_institution_id',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // name
            'B' => 15, // classroom_id
            'C' => 15, // advisor_id
            'D' => 25, // educational_institution_id
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
