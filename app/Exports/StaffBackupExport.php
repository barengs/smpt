<?php

namespace App\Exports;

use App\Models\Staff;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Staff::all();
    }

    /**
     * @param mixed $staff
     * @return array
     */
    public function map($staff): array
    {
        return [
            $staff->id,
            $staff->user_id,
            $staff->code,
            $staff->first_name,
            $staff->last_name,
            $staff->email,
            $staff->phone,
            $staff->address,
            $staff->nik,
            $staff->nip,
            $staff->gender,
            $staff->village_id,
            $staff->zip_code,
            $staff->marital_status,
            $staff->status,
            $staff->job_id,
            $staff->photo,
            $staff->created_at ? $staff->created_at->format('Y-m-d H:i:s') : null,
            $staff->updated_at ? $staff->updated_at->format('Y-m-d H:i:s') : null,
            $staff->deleted_at ? $staff->deleted_at->format('Y-m-d H:i:s') : null,
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
            'code',
            'first_name',
            'last_name',
            'email',
            'phone',
            'address',
            'nik',
            'nip',
            'gender',
            'village_id',
            'zip_code',
            'marital_status',
            'status',
            'job_id',
            'photo',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
