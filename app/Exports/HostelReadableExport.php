<?php

namespace App\Exports;

use App\Models\Hostel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HostelReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Hostel::with('program')->get();
    }

    /**
     * @param mixed $hostel
     * @return array
     */
    public function map($hostel): array
    {
        return [
            $hostel->name,
            $hostel->program ? $hostel->program->name : '-',
            $hostel->capacity,
            $hostel->description ?? '-',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Asrama',
            'Program',
            'Kapasitas',
            'Deskripsi',
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
