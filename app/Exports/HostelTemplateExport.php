<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HostelTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Sample data
        return [
            [
                '1',
                'Asrama Al-Azhar',
                '50',
                'Asrama putra lantai 1',
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'program_id',
            'name',
            'capacity',
            'description',
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12, // program_id
            'B' => 25, // name
            'C' => 12, // capacity
            'D' => 40, // description
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
