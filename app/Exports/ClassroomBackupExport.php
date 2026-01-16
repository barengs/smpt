<?php

namespace App\Exports;

use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassroomBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Classroom::withTrashed()->get();
    }

    /**
     * @param mixed $classroom
     * @return array
     */
    public function map($classroom): array
    {
        return [
            $classroom->id,
            $classroom->educational_institution_id,
            $classroom->name,
            $classroom->level,
            $classroom->created_at ? $classroom->created_at->format('Y-m-d H:i:s') : null,
            $classroom->updated_at ? $classroom->updated_at->format('Y-m-d H:i:s') : null,
            $classroom->deleted_at ? $classroom->deleted_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'educational_institution_id',
            'name',
            'level',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
