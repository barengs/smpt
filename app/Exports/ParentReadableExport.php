<?php

namespace App\Exports;

use App\Models\ParentProfile;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParentReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ParentProfile::with(['user', 'occupation', 'education', 'student'])->get();
    }

    /**
     * @param mixed $parent
     * @return array
     */
    public function map($parent): array
    {
        // Format students list
        $students = $parent->student->map(function($student) {
            return $student->nama_lengkap ?? $student->name ?? '-';
        })->join(', ');

        return [
            $parent->first_name . ' ' . $parent->last_name,
            "'" . $parent->nik, // Force string format for NIK to prevent scientific notation
            "'" . $parent->kk,
            $parent->gender == 'L' ? 'Laki-laki' : 'Perempuan',
            ucfirst($parent->parent_as),
            $parent->phone,
            $parent->email,
            $parent->occupation ? $parent->occupation->name : '-',
            $parent->education ? $parent->education->name : '-',
            $parent->domicile_address,
            $students
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'NIK',
            'KK',
            'Jenis Kelamin',
            'Status Orang Tua',
            'No. Telepon',
            'Email',
            'Pekerjaan',
            'Pendidikan',
            'Alamat Domisili',
            'Siswa Terkait'
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
