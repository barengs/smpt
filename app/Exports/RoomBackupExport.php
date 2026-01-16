<?php

namespace App\Exports;

use App\Models\Room;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RoomBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Room::withTrashed()->get();
    }

    /**
     * @param mixed $room
     * @return array
     */
    public function map($room): array
    {
        return [
            $room->id,
            $room->hostel_id,
            $room->name,
            $room->capacity,
            $room->description,
            $room->is_active,
            $room->created_at ? $room->created_at->format('Y-m-d H:i:s') : null,
            $room->updated_at ? $room->updated_at->format('Y-m-d H:i:s') : null,
            $room->deleted_at ? $room->deleted_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'hostel_id',
            'name',
            'capacity',
            'description',
            'is_active',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
