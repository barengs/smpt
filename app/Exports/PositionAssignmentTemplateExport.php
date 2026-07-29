<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PositionAssignmentTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    public function array(): array
    {
        return [
            [
                '1', // position_id
                '1', // staff_id
                '1', // academic_year_id (optional)
                '1', // hostel_id (optional)
                '2026-07-01', // start_date
                '2027-06-30', // end_date (optional)
                'SK-123/ORG/2026', // assignment_letter (optional)
                'Penugasan pengurus baru', // notes (optional)
                '1', // is_active
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'position_id',
            'staff_id',
            'academic_year_id',
            'hostel_id',
            'start_date',
            'end_date',
            'assignment_letter',
            'notes',
            'is_active',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 25,
            'H' => 25,
            'I' => 10,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
