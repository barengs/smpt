<?php

namespace App\Exports;

use App\Models\Staff;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StaffReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Staff::with(['user.roles', 'village'])->get();
    }

    /**
     * @param mixed $staff
     * @return array
     */
    public function map($staff): array
    {
        $roles = $staff->user ? $staff->user->getRoleNames()->implode(', ') : '-';
        $villageName = $staff->village ? $staff->village : '-'; // Assuming village is a string accessor or relation with string repr

        return [
            $staff->code,
            $staff->first_name . ' ' . $staff->last_name,
            $staff->gender == 'L' ? 'Laki-laki' : 'Perempuan',
            $staff->nip ?? '-',
            $staff->nik ?? '-',
            $roles,
            $staff->email,
            $staff->phone ?? '-',
            $staff->address ?? '-',
            $villageName,
            $staff->status,
            $staff->marital_status ?? '-',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Pegawai',
            'Nama Lengkap',
            'Jenis Kelamin',
            'NIP',
            'NIK',
            'Peran (Role)',
            'Email',
            'No. Telepon',
            'Alamat',
            'Desa/Kelurahan',
            'Status',
            'Status Pernikahan',
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
