<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RoomTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        // One sample row
        return [
            [
                '1',
                'Kamar Abu Bakar 01',
                '4',
                'Kamar santri baru',
                '1'
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'hostel_id',
            'name',
            'capacity',
            'description',
            'is_active',
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12, // hostel_id
            'B' => 25, // name
            'C' => 12, // capacity
            'D' => 30, // description
            'E' => 12, // is_active
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
