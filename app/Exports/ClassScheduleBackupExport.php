<?php

namespace App\Exports;

use App\Models\ClassSchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassScheduleBackupExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ClassSchedule::all();
    }

    /**
     * @param mixed $schedule
     * @return array
     */
    public function map($schedule): array
    {
        return [
            $schedule->id,
            $schedule->academic_year_id,
            $schedule->educational_institution_id,
            $schedule->session,
            $schedule->status,
            $schedule->created_at ? $schedule->created_at->format('Y-m-d H:i:s') : null,
            $schedule->updated_at ? $schedule->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'academic_year_id',
            'educational_institution_id',
            'session',
            'status',
            'created_at',
            'updated_at',
        ];
    }
}
