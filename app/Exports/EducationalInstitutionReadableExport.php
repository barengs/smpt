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
            $institution->name,
            $institution->email,
            $institution->website,
            $institution->phone_number,
            $institution->headmaster ? $institution->headmaster->full_name : '-',
            $institution->education ? $institution->education->name : '-',
            $institution->address,
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
