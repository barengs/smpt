<?php

namespace App\Exports;

use App\Models\Education;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EducationBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Education::withTrashed()->get();
    }

    /**
     * @param mixed $education
     * @return array
     */
    public function map($education): array
    {
        return [
            $education->id,
            $education->name,
            $education->level,
            $education->description,
            $education->created_at ? $education->created_at->format('Y-m-d H:i:s') : null,
            $education->updated_at ? $education->updated_at->format('Y-m-d H:i:s') : null,
            $education->deleted_at ? $education->deleted_at->format('Y-m-d H:i:s') : null,
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
            'level',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
