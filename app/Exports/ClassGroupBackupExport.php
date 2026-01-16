<?php

namespace App\Exports;

use App\Models\ClassGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassGroupBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ClassGroup::withTrashed()->get();
    }

    /**
     * @param mixed $classGroup
     * @return array
     */
    public function map($classGroup): array
    {
        return [
            $classGroup->id,
            $classGroup->educational_institution_id,
            $classGroup->classroom_id,
            $classGroup->advisor_id,
            $classGroup->name,
            $classGroup->status,
            $classGroup->created_at ? $classGroup->created_at->format('Y-m-d H:i:s') : null,
            $classGroup->updated_at ? $classGroup->updated_at->format('Y-m-d H:i:s') : null,
            $classGroup->deleted_at ? $classGroup->deleted_at->format('Y-m-d H:i:s') : null,
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
            'classroom_id',
            'advisor_id',
            'name',
            'status',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
