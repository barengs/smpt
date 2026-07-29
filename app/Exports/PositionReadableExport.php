<?php

namespace App\Exports;

use App\Models\Position;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PositionReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Position::with(['organization', 'parent'])->get();
    }

    public function map($position): array
    {
        return [
            $position->name,
            $position->code,
            $position->description,
            $position->organization ? $position->organization->name : '-',
            $position->parent ? $position->parent->name : '-',
            $position->level,
            $position->is_active ? 'Aktif' : 'Tidak Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Jabatan',
            'Kode',
            'Deskripsi',
            'Organisasi',
            'Jabatan Atasan',
            'Level',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
