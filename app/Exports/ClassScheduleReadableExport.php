<?php

namespace App\Exports;

use App\Models\ClassSchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassScheduleReadableExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Get schedules with all necessary relations
        $schedules = ClassSchedule::with([
            'academicYear',
            'education',
            'details.classroom',
            'details.classGroup',
            'details.lessonHour',
            'details.teacher',
            'details.study'
        ])->get();

        $rows = collect();

        foreach ($schedules as $schedule) {
            foreach ($schedule->details as $detail) {
                // Flatten the structure: Header info + Detail info
                $rows->push([
                    'academic_year' => $schedule->academicYear ? $schedule->academicYear->name : '-',
                    'education' => $schedule->education ? $schedule->education->name : '-',
                    'session' => $schedule->session,
                    'status' => $schedule->status,
                    'day' => ucfirst($detail->day),
                    'time' => $detail->lessonHour ? $detail->lessonHour->name : '-', // Assuming name is something like "07:00 - 08:00" or just Label
                    'subject' => $detail->study ? $detail->study->name : '-',
                    'teacher' => $detail->teacher ? $detail->teacher->full_name : '-',
                    'classroom' => $detail->classroom ? $detail->classroom->name : '-',
                    'class_group' => $detail->classGroup ? $detail->classGroup->name : '-',
                ]);
            }
        }

        return $rows;
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        // Since we already formatted in collection(), just return the array values
        return array_values($row);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tahun Ajaran',
            'Institusi Pendidikan',
            'Sesi',
            'Status Jadwal',
            'Hari',
            'Jam Pelajaran',
            'Mata Pelajaran',
            'Guru Pengampu',
            'Kelas',
            'Rombel',
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
