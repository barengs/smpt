<?php

namespace App\Exports;

use App\Models\EducationClass;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EducationClassBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return EducationClass::withTrashed()->get();
    }

    /**
     * @param mixed $educationClass
     * @return array
     */
    public function map($educationClass): array
    {
        return [
            $educationClass->id,
            $educationClass->name,
            $educationClass->description,
            $educationClass->created_at ? $educationClass->created_at->format('Y-m-d H:i:s') : null,
            $educationClass->updated_at ? $educationClass->updated_at->format('Y-m-d H:i:s') : null,
            $educationClass->deleted_at ? $educationClass->deleted_at->format('Y-m-d H:i:s') : null,
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
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
