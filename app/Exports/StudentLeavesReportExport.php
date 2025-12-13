<?php

namespace App\Exports;

use App\Models\StudentLeave;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class StudentLeavesReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithTitle
{
    protected $filters;
    protected $period;
    protected $startDate;
    protected $endDate;

    /**
     * Constructor
     *
     * @param array $filters - Query filters (student_id, leave_type_id, status, academic_year_id)
     * @param string $period - Filter period: 'monthly', 'quarterly', 'yearly'
     * @param string|null $year - Year for filtering (e.g., '2024')
     * @param string|null $month - Month for filtering (1-12, only for monthly)
     * @param string|null $quarter - Quarter for filtering (1-4, only for quarterly)
     */
    public function __construct(array $filters = [], string $period = 'monthly', ?string $year = null, ?string $month = null, ?string $quarter = null)
    {
        $this->filters = $filters;
        $this->period = $period;

        // Set date range based on period
        $this->setDateRange($period, $year, $month, $quarter);
    }

    /**
     * Set date range based on period filter
     */
    protected function setDateRange(string $period, ?string $year, ?string $month, ?string $quarter): void
    {
        $year = $year ?? now()->year;

        switch ($period) {
            case 'monthly':
                $month = $month ?? now()->month;
                $this->startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $this->endDate = Carbon::create($year, $month, 1)->endOfMonth();
                break;

            case 'quarterly':
                $quarter = $quarter ?? ceil(now()->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $this->startDate = Carbon::create($year, $startMonth, 1)->startOfMonth();
                $this->endDate = Carbon::create($year, $startMonth, 1)->addMonths(2)->endOfMonth();
                break;

            case 'yearly':
                $this->startDate = Carbon::create($year, 1, 1)->startOfYear();
                $this->endDate = Carbon::create($year, 12, 31)->endOfYear();
                break;

            default:
                // Default to current month
                $this->startDate = now()->startOfMonth();
                $this->endDate = now()->endOfMonth();
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = StudentLeave::with([
            'student:id,first_name,last_name,nis',
            'leaveType:id,name',
            'academicYear:id,year',
            'approver:id,first_name,last_name',
            'creator:id,first_name,last_name',
            'report',
            'penalties.sanction:id,name'
        ]);

        // Apply date range filter
        $query->whereBetween('start_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

        // Apply additional filters
        if (!empty($this->filters['student_id'])) {
            $query->where('student_id', $this->filters['student_id']);
        }

        if (!empty($this->filters['leave_type_id'])) {
            $query->where('leave_type_id', $this->filters['leave_type_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['academic_year_id'])) {
            $query->where('academic_year_id', $this->filters['academic_year_id']);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    /**
     * Map data to Excel rows
     *
     * @param mixed $leave
     * @return array
     */
    public function map($leave): array
    {
        return [
            $leave->leave_number,
            $leave->student ? ($leave->student->first_name . ' ' . $leave->student->last_name) : '-',
            $leave->student ? $leave->student->nis : '-',
            $leave->leaveType ? $leave->leaveType->name : '-',
            $leave->academicYear ? $leave->academicYear->year : '-',
            $leave->start_date ? Carbon::parse($leave->start_date)->format('d/m/Y') : '-',
            $leave->end_date ? Carbon::parse($leave->end_date)->format('d/m/Y') : '-',
            $leave->duration_days . ' hari',
            $leave->reason,
            $leave->destination ?? '-',
            $leave->contact_person ?? '-',
            $leave->contact_phone ?? '-',
            $this->getStatusLabel($leave->status),
            $leave->approver ? ($leave->approver->first_name . ' ' . $leave->approver->last_name) : '-',
            $leave->approved_at ? Carbon::parse($leave->approved_at)->format('d/m/Y H:i') : '-',
            $leave->approval_notes ?? '-',
            $leave->expected_return_date ? Carbon::parse($leave->expected_return_date)->format('d/m/Y') : '-',
            $leave->actual_return_date ? Carbon::parse($leave->actual_return_date)->format('d/m/Y') : '-',
            $leave->report ? ($leave->report->is_late ? 'Ya' : 'Tidak') : '-',
            $leave->report ? ($leave->report->late_days . ' hari') : '-',
            $leave->report ? $leave->report->condition : '-',
            $leave->has_penalty ? 'Ya' : 'Tidak',
            $this->getPenaltiesDescription($leave->penalties),
            $leave->creator ? ($leave->creator->first_name . ' ' . $leave->creator->last_name) : '-',
            $leave->created_at ? Carbon::parse($leave->created_at)->format('d/m/Y H:i') : '-',
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
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'active' => 'Aktif',
            'completed' => 'Selesai',
            'overdue' => 'Terlambat',
            'cancelled' => 'Dibatalkan',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get penalties description
     *
     * @param \Illuminate\Database\Eloquent\Collection $penalties
     * @return string
     */
    protected function getPenaltiesDescription($penalties): string
    {
        if ($penalties->isEmpty()) {
            return '-';
        }

        return $penalties->map(function ($penalty) {
            $sanction = $penalty->sanction ? $penalty->sanction->name : $penalty->penalty_type;
            return $sanction . ' (' . $penalty->point_value . ' poin)';
        })->join('; ');
    }

    /**
     * Excel column headings
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'No. Izin',
            'Nama Santri',
            'NIS',
            'Jenis Izin',
            'Tahun Akademik',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi',
            'Alasan',
            'Tujuan',
            'Nama Kontak',
            'No. HP Kontak',
            'Status',
            'Disetujui Oleh',
            'Tanggal Disetujui',
            'Catatan Persetujuan',
            'Tanggal Kembali Diharapkan',
            'Tanggal Kembali Aktual',
            'Terlambat',
            'Hari Keterlambatan',
            'Kondisi Saat Kembali',
            'Ada Sanksi',
            'Detail Sanksi',
            'Dibuat Oleh',
            'Tanggal Dibuat',
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
            'A' => 18,  // No. Izin
            'B' => 25,  // Nama Santri
            'C' => 12,  // NIS
            'D' => 18,  // Jenis Izin
            'E' => 15,  // Tahun Akademik
            'F' => 15,  // Tanggal Mulai
            'G' => 15,  // Tanggal Selesai
            'H' => 10,  // Durasi
            'I' => 35,  // Alasan
            'J' => 20,  // Tujuan
            'K' => 20,  // Nama Kontak
            'L' => 15,  // No. HP Kontak
            'M' => 12,  // Status
            'N' => 20,  // Disetujui Oleh
            'O' => 18,  // Tanggal Disetujui
            'P' => 30,  // Catatan Persetujuan
            'Q' => 20,  // Tanggal Kembali Diharapkan
            'R' => 20,  // Tanggal Kembali Aktual
            'S' => 12,  // Terlambat
            'T' => 15,  // Hari Keterlambatan
            'U' => 15,  // Kondisi Saat Kembali
            'V' => 12,  // Ada Sanksi
            'W' => 35,  // Detail Sanksi
            'X' => 20,  // Dibuat Oleh
            'Y' => 18,  // Tanggal Dibuat
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
            'monthly' => 'Bulanan - ' . $this->startDate->format('F Y'),
            'quarterly' => 'Triwulan - Q' . ceil($this->startDate->month / 3) . ' ' . $this->startDate->year,
            'yearly' => 'Tahunan - ' . $this->startDate->year,
            default => 'Laporan Izin'
        };

        return 'Laporan Izin ' . $periodLabel;
    }
}
