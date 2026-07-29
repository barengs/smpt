<?php

namespace App\Exports;

use App\Models\PositionAssignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PositionAssignmentReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return PositionAssignment::with(['position.organization', 'staff', 'academicYear', 'hostel'])->get();
    }

    public function map($assignment): array
    {
        return [
            $assignment->position ? $assignment->position->name : '-',
            $assignment->position && $assignment->position->organization ? $assignment->position->organization->name : '-',
            $assignment->staff ? trim($assignment->staff->first_name . ' ' . $assignment->staff->last_name) : '-',
            $assignment->academicYear ? $assignment->academicYear->year : '-',
            $assignment->hostel ? $assignment->hostel->name : '-',
            $assignment->start_date ? $assignment->start_date->format('Y-m-d') : '-',
            $assignment->end_date ? $assignment->end_date->format('Y-m-d') : '-',
            $assignment->is_active ? 'Aktif' : 'Tidak Aktif',
            $assignment->notes,
        ];
    }

    public function headings(): array
    {
        return [
            'Jabatan',
            'Organisasi/Lembaga',
            'Nama Staf/Pejabat',
            'Tahun Ajaran',
            'Asrama',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
            'Keterangan/Catatan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
