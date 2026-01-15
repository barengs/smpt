<?php

namespace App\Exports;

use App\Models\Study;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudyBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Study::withTrashed()->get();
    }

    /**
     * @param mixed $study
     * @return array
     */
    public function map($study): array
    {
        return [
            $study->id,
            $study->name,
            $study->description,
            $study->created_at ? $study->created_at->format('Y-m-d H:i:s') : null,
            $study->updated_at ? $study->updated_at->format('Y-m-d H:i:s') : null,
            $study->deleted_at ? $study->deleted_at->format('Y-m-d H:i:s') : null,
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
