<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PositionTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    public function array(): array
    {
        return [
            [
                'Kepala MI', // name
                'KEP-MI', // code
                'Kepala Sekolah MI', // description
                '1', // organization_id
                '', // parent_id (optional)
                '1', // level
                '1', // is_active
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'code',
            'description',
            'organization_id',
            'parent_id',
            'level',
            'is_active',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 25,
            'D' => 15,
            'E' => 15,
            'F' => 10,
            'G' => 10,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
