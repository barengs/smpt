<?php

namespace App\Exports;

use App\Models\StudentViolation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class StudentViolationsReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithTitle
{
    protected $filters;
    protected $period;
    protected $startDate;
    protected $endDate;

    /**
     * Constructor
     *
     * @param array $filters - Query filters (student_id, category_id, status, academic_year_id)
     * @param string $period - Filter period: 'daily', 'weekly', 'monthly', 'custom'
     * @param string|null $dateFrom - Custom start date
     * @param string|null $dateTo - Custom end date
     */
    public function __construct(array $filters = [], string $period = 'monthly', ?string $dateFrom = null, ?string $dateTo = null)
    {
        $this->filters = $filters;
        $this->period = $period;

        // Set date range based on period
        $this->setDateRange($period, $dateFrom, $dateTo);
    }

    /**
     * Set date range based on period filter
     */
    protected function setDateRange(string $period, ?string $dateFrom, ?string $dateTo): void
    {
        switch ($period) {
            case 'daily':
                $this->startDate = now()->startOfDay();
                $this->endDate = now()->endOfDay();
                break;

            case 'weekly':
                $this->startDate = now()->startOfWeek();
                $this->endDate = now()->endOfWeek();
                break;

            case 'monthly':
                $this->startDate = now()->startOfMonth();
                $this->endDate = now()->endOfMonth();
                break;

            case 'custom':
                $this->startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->startOfMonth();
                $this->endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfMonth();
                break;

            default:
                // Default to current month if unknown period
                $this->startDate = now()->startOfMonth();
                $this->endDate = now()->endOfMonth();
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = StudentViolation::with([
            'student:id,first_name,last_name,nis',
            'violation.category',
            'reporter:id,first_name,last_name',
            'academicYear:id,year'
        ]);

        // Apply date range filter
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('violation_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);
        }

        // Apply additional filters
        if (!empty($this->filters['student_id'])) {
            $query->where('student_id', $this->filters['student_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['academic_year_id'])) {
            $query->where('academic_year_id', $this->filters['academic_year_id']);
        }

        return $query->orderBy('violation_date', 'desc')->get();
    }

    /**
     * Map data to Excel rows
     *
     * @param mixed $violation
     * @return array
     */
    public function map($violation): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $violation->violation_date ? Carbon::parse($violation->violation_date)->format('d/m/Y') : '-',
            $violation->violation_time ? Carbon::parse($violation->violation_time)->format('H:i') : '-',
            $violation->student ? $violation->student->nis : '-',
            $violation->student ? ($violation->student->first_name . ' ' . $violation->student->last_name) : '-',
            $violation->violation ? $violation->violation->name : '-',
            $violation->violation && $violation->violation->category ? $violation->violation->category->name : '-',
            $violation->violation ? $violation->violation->point : 0,
            $violation->reporter ? ($violation->reporter->first_name . ' ' . $violation->reporter->last_name) : '-',
            $this->getStatusLabel($violation->status),
            $violation->notes ?? '-',
        ];
    }

    /**
     * Get status label in Indonesian
     *
     * @param string $status
     * @return string
     */
    protected function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'Menunggu',
            'verified' => 'Terverifikasi',
            'processed' => 'Diproses',
            'cancelled' => 'Dibatalkan',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Excel column headings
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Waktu',
            'NIS',
            'Nama Santri',
            'Pelanggaran',
            'Kategori',
            'Poin',
            'Pelapor',
            'Status',
            'Catatan',
        ];
    }

    /**
     * Set column widths
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // Tanggal
            'C' => 10,  // Waktu
            'D' => 15,  // NIS
            'E' => 25,  // Nama Santri
            'F' => 30,  // Pelanggaran
            'G' => 20,  // Kategori
            'H' => 10,  // Poin
            'I' => 20,  // Pelapor
            'J' => 15,  // Status
            'K' => 35,  // Catatan
        ];
    }

    /**
     * Apply styles to the worksheet
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Bold header row
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F4F8']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    /**
     * Worksheet title
     *
     * @return string
     */
    public function title(): string
    {
        $periodLabel = match($this->period) {
            'daily' => 'Harian - ' . $this->startDate->format('d/m/Y'),
            'weekly' => 'Mingguan - ' . $this->startDate->format('d/m') . ' s.d ' . $this->endDate->format('d/m/Y'),
            'monthly' => 'Bulanan - ' . $this->startDate->format('F Y'),
            'custom' => 'Periode - ' . $this->startDate->format('d/m/Y') . ' s.d ' . $this->endDate->format('d/m/Y'),
            default => 'Laporan Pelanggaran'
        };

        return 'Laporan ' . $periodLabel;
    }
}
