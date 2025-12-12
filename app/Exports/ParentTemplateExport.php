<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParentTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Return sample data
        return [
            [
                '1234567890123456',
                '1234567890123456',
                'Ahmad',
                'Santoso',
                'L',
                'ayah',
                'Jl. Merdeka No. 123, Jakarta',
                'Jl. Sudirman No. 456, Jakarta',
                '081234567890',
                'ahmad@example.com',
                '1',
                '1'
            ],
            [
                '1234567890123457',
                '1234567890123456',
                'Siti',
                'Rahayu',
                'P',
                'ibu',
                'Jl. Merdeka No. 123, Jakarta',
                'Jl. Sudirman No. 456, Jakarta',
                '081234567891',
                'siti@example.com',
                '2',
                '2'
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'nik',
            'kk',
            'first_name',
            'last_name',
            'gender',
            'parent_as',
            'card_address',
            'domicile_address',
            'phone',
            'email',
            'occupation_id',
            'education_id'
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // nik
            'B' => 20,  // kk
            'C' => 20,  // first_name
            'D' => 20,  // last_name
            'E' => 10,  // gender
            'F' => 12,  // parent_as
            'G' => 35,  // card_address
            'H' => 35,  // domicile_address
            'I' => 15,  // phone
            'J' => 25,  // email
            'K' => 15,  // occupation_id
            'L' => 15,  // education_id
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
