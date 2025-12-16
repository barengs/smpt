<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StaffTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * Sample data for the template
     *
     * @return array
     */
    public function array(): array
    {
        return [
            [
                'Ahmad',                    // first_name
                'Santoso',                  // last_name
                'ahmad.santoso@example.com', // email
                'L',                        // gender
                '3528061508860021',         // nik
                '198608151234567890',       // nip
                '081234567890',             // phone
                'Jl. Merdeka No. 123',      // address
                '12345',                    // zip_code
                '1',                        // village_id
                '1',                        // job_id
                'Surabaya',                 // birth_place
                '1986-08-15',               // birth_date
                'Menikah',                  // marital_status
                'Aktif',                    // status
                'asatidz',                  // role
            ],
            [
                'Siti',                     // first_name
                'Rahayu',                   // last_name
                'siti.rahayu@example.com',  // email
                'P',                        // gender
                '3528064512900001',         // nik
                '199012051234567891',       // nip
                '081234567891',             // phone
                'Jl. Sudirman No. 456',     // address
                '12346',                    // zip_code
                '2',                        // village_id
                '2',                        // job_id
                'Jakarta',                  // birth_place
                '1990-12-05',               // birth_date
                'Belum Menikah',            // marital_status
                'Aktif',                    // status
                'staf',                     // role
            ],
        ];
    }

    /**
     * Column headings
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'first_name',       // Required - First name
            'last_name',        // Optional - Last name
            'email',            // Required - Email (also used as login)
            'gender',           // Required - L (Laki-laki) or P (Perempuan)
            'nik',              // Optional - NIK (16 digits)
            'nip',              // Optional - NIP
            'phone',            // Optional - Phone number
            'address',          // Optional - Address
            'zip_code',         // Optional - Zip/Postal code
            'village_id',       // Optional - Village ID from Indonesia package
            'job_id',           // Optional - Job/Profession ID
            'birth_place',      // Optional - Birth place
            'birth_date',       // Optional - Birth date (YYYY-MM-DD)
            'marital_status',   // Optional - Belum Menikah/Menikah/Cerai/Duda/Janda
            'status',           // Optional - Aktif/Tidak Aktif (default: Aktif)
            'role',             // Optional - Role name (default: staf)
        ];
    }

    /**
     * Set column widths
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // first_name
            'B' => 20,  // last_name
            'C' => 30,  // email
            'D' => 10,  // gender
            'E' => 20,  // nik
            'F' => 22,  // nip
            'G' => 15,  // phone
            'H' => 35,  // address
            'I' => 12,  // zip_code
            'J' => 12,  // village_id
            'K' => 12,  // job_id
            'L' => 20,  // birth_place
            'M' => 15,  // birth_date
            'N' => 18,  // marital_status
            'O' => 15,  // status
            'P' => 15,  // role
        ];
    }

    /**
     * Apply styles to the worksheet
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text with background color
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F4F8']
                ],
            ],
        ];
    }
}
