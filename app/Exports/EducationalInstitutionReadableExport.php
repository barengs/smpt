<?php

namespace App\Exports;

use App\Models\EducationalInstitution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EducationalInstitutionReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return EducationalInstitution::with(['headmaster', 'education'])->get();
    }

    /**
     * @param mixed $institution
     * @return array
     */
    public function map($institution): array
    {
        return [
            $institution->institution_name,
            $institution->institution_email,
            $institution->institution_website,
            $institution->institution_phone,
            $institution->headmaster ? trim($institution->headmaster->first_name . ' ' . $institution->headmaster->last_name) : '-',
            $institution->education ? $institution->education->name : '-',
            $institution->institution_address,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Institusi',
            'Email',
            'Website',
            'No. Telepon',
            'Kepala Sekolah/Institusi',
            'Jenjang Pendidikan',
            'Alamat',
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
