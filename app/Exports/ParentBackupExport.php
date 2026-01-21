<?php

namespace App\Exports;

use App\Models\ParentProfile;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ParentBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ParentProfile::all();
    }

    /**
     * @param mixed $parent
     * @return array
     */
    public function map($parent): array
    {
        return [
            $parent->id,
            $parent->user_id,
            $parent->first_name,
            $parent->last_name,
            $parent->nik,
            $parent->kk,
            $parent->gender,
            $parent->parent_as, // ayah / ibu
            $parent->card_address,
            $parent->domicile_address,
            $parent->phone,
            $parent->email,
            $parent->occupation_id,
            $parent->education_id,
            $parent->photo,
            $parent->created_at ? $parent->created_at->format('Y-m-d H:i:s') : null,
            $parent->updated_at ? $parent->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'user_id',
            'first_name',
            'last_name',
            'nik',
            'kk',
            'gender',
            'parent_as',
            'card_address',
            'domicile_address',
            'phone',
            'email',
            'occupation_id',
            'education_id',
            'photo',
            'created_at',
            'updated_at',
        ];
    }
}
