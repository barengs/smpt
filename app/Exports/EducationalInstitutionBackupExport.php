<?php

namespace App\Exports;

use App\Models\EducationalInstitution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EducationalInstitutionBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // No SoftDeletes trait in model, so simply all()
        return EducationalInstitution::all();
    }

    /**
     * @param mixed $institution
     * @return array
     */
    public function map($institution): array
    {
        return [
            $institution->id,
            $institution->education_id,
            $institution->education_class_id,
            $institution->headmaster_id,
            $institution->name,
            $institution->email,
            $institution->website,
            $institution->phone_number,
            $institution->address,
            $institution->created_at ? $institution->created_at->format('Y-m-d H:i:s') : null,
            $institution->updated_at ? $institution->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'education_id',
            'education_class_id',
            'headmaster_id',
            'name',
            'email',
            'website',
            'phone_number',
            'address',
            'created_at',
            'updated_at',
        ];
    }
}
