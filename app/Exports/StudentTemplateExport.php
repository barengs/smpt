<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Return sample data
        return [
            [
                '2024001',
                'John',
                'Doe',
                'L',
                '1',
                'NIK001',
                '2024',
                '1234567890123456',
                '1234567890123456',
                'Jl. Contoh No. 123',
                'Jakarta',
                '2005-01-15',
                'SMP',
                '1',
                'Desa Contoh',
                'Kec. Contoh',
                '12345',
                '081234567890',
                '1',
                'Aktif'
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'nis',
            'first_name',
            'last_name',
            'gender',
            'program_id',
            'parent_id',
            'period',
            'nik',
            'kk',
            'address',
            'born_in',
            'born_at',
            'last_education',
            'village_id',
            'village',
            'district',
            'postal_code',
            'phone',
            'hostel_id',
            'status'
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // nis
            'B' => 20,  // first_name
            'C' => 20,  // last_name
            'D' => 10,  // gender
            'E' => 12,  // program_id
            'F' => 15,  // parent_id
            'G' => 10,  // period
            'H' => 20,  // nik
            'I' => 20,  // kk
            'J' => 30,  // address
            'K' => 20,  // born_in
            'L' => 15,  // born_at
            'M' => 20,  // last_education
            'N' => 12,  // village_id
            'O' => 20,  // village
            'P' => 20,  // district
            'Q' => 12,  // postal_code
            'R' => 15,  // phone
            'S' => 12,  // hostel_id
            'T' => 15,  // status
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
