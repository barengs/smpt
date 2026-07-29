<?php

namespace App\Exports;

use App\Models\PositionAssignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PositionAssignmentBackupExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return PositionAssignment::all();
    }

    public function map($assignment): array
    {
        return [
            $assignment->id,
            $assignment->position_id,
            $assignment->staff_id,
            $assignment->academic_year_id,
            $assignment->hostel_id,
            $assignment->start_date ? $assignment->start_date->format('Y-m-d') : null,
            $assignment->end_date ? $assignment->end_date->format('Y-m-d') : null,
            $assignment->assignment_letter,
            $assignment->notes,
            $assignment->is_active ? 1 : 0,
            $assignment->created_at ? $assignment->created_at->format('Y-m-d H:i:s') : null,
            $assignment->updated_at ? $assignment->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    public function headings(): array
    {
        return [
            'id',
            'position_id',
            'staff_id',
            'academic_year_id',
            'hostel_id',
            'start_date',
            'end_date',
            'assignment_letter',
            'notes',
            'is_active',
            'created_at',
            'updated_at',
        ];
    }
}
