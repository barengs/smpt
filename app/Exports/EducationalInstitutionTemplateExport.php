<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EducationalInstitutionTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    public function array(): array
    {
        return [
            [
                '1', // education_id
                '1', // education_class_id
                'MI Panyeppen', // institution_name
                'active', // institution_status
                'Panyeppen', // institution_description
                '1', // headmaster_id
                '123456789', // registration_number
                'Jl. Panyeppen No. 1', // institution_address
                '08123456789', // institution_phone
                'info@mi-panyeppen.sch.id', // institution_email
                'https://mi-panyeppen.sch.id', // institution_website
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'education_id',
            'education_class_id',
            'institution_name',
            'institution_status',
            'institution_description',
            'headmaster_id',
            'registration_number',
            'institution_address',
            'institution_phone',
            'institution_email',
            'institution_website',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 25,
            'D' => 15,
            'E' => 25,
            'F' => 15,
            'G' => 20,
            'H' => 25,
            'I' => 15,
            'J' => 25,
            'K' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
