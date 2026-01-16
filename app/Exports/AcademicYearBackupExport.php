<?php

namespace App\Exports;

use App\Models\AcademicYear;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AcademicYearBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return AcademicYear::withTrashed()->get();
    }

    /**
     * @param mixed $academicYear
     * @return array
     */
    public function map($academicYear): array
    {
        return [
            $academicYear->id,
            $academicYear->name,
            $academicYear->start_date ? $academicYear->start_date->format('Y-m-d') : null,
            $academicYear->end_date ? $academicYear->end_date->format('Y-m-d') : null,
            $academicYear->active,
            $academicYear->created_at ? $academicYear->created_at->format('Y-m-d H:i:s') : null,
            $academicYear->updated_at ? $academicYear->updated_at->format('Y-m-d H:i:s') : null,
            $academicYear->deleted_at ? $academicYear->deleted_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'name',
            'start_date',
            'end_date',
            'active',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
