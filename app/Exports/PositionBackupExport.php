<?php

namespace App\Exports;

use App\Models\Position;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PositionBackupExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Position::withTrashed()->get();
    }

    public function map($position): array
    {
        return [
            $position->id,
            $position->organization_id,
            $position->parent_id,
            $position->name,
            $position->code,
            $position->description,
            $position->level,
            $position->is_active ? 1 : 0,
            $position->created_at ? $position->created_at->format('Y-m-d H:i:s') : null,
            $position->updated_at ? $position->updated_at->format('Y-m-d H:i:s') : null,
            $position->deleted_at ? $position->deleted_at->format('Y-m-d H:i:s') : null,
        ];
    }

    public function headings(): array
    {
        return [
            'id',
            'organization_id',
            'parent_id',
            'name',
            'code',
            'description',
            'level',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
