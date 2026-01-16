<?php

namespace App\Exports;

use App\Models\Hostel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HostelBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Hostel::withTrashed()->get();
    }

    /**
     * @param mixed $hostel
     * @return array
     */
    public function map($hostel): array
    {
        return [
            $hostel->id,
            $hostel->program_id,
            $hostel->name,
            $hostel->capacity,
            $hostel->description,
            $hostel->created_at ? $hostel->created_at->format('Y-m-d H:i:s') : null,
            $hostel->updated_at ? $hostel->updated_at->format('Y-m-d H:i:s') : null,
            $hostel->deleted_at ? $hostel->deleted_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'program_id',
            'name',
            'capacity',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
