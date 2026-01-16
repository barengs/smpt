<?php

namespace App\Exports;

use App\Models\Room;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RoomReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Room::with('hostel')->get();
    }

    /**
     * @param mixed $room
     * @return array
     */
    public function map($room): array
    {
        return [
            $room->name,
            $room->hostel ? $room->hostel->name : '-',
            $room->capacity,
            $room->is_active ? 'Aktif' : 'Tidak Aktif',
            $room->description ?? '-',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Ruangan',
            'Asrama',
            'Kapasitas',
            'Status',
            'Deskripsi',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
