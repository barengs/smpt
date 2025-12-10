<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class StudentLeave extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'approved_at' => 'datetime',
        'has_penalty' => 'boolean',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function approver()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    public function report()
    {
        return $this->hasOne(StudentLeaveReport::class, 'student_leave_id');
    }

    public function penalties()
    {
        return $this->hasMany(StudentLeavePenalty::class, 'student_leave_id');
    }

    // Helper methods
    public function isOverdue()
    {
        if ($this->status === 'active' && $this->expected_return_date) {
            return Carbon::now()->isAfter($this->expected_return_date);
        }
        return false;
    }

    public function getDaysLate()
    {
        if ($this->expected_return_date) {
            $returnDate = $this->actual_return_date ?? Carbon::now();
            $expectedDate = Carbon::parse($this->expected_return_date);

            if ($returnDate->isAfter($expectedDate)) {
                return $returnDate->diffInDays($expectedDate);
            }
        }
        return 0;
    }

    public function canBeReported()
    {
        return in_array($this->status, ['approved', 'active', 'overdue']);
    }

    /**
     * Convert Gregorian date to Hijri date
     *
     * @param Carbon|null $date
     * @return array ['year' => int, 'month' => int, 'day' => int]
     */
    private static function gregorianToHijri($date = null)
    {
        $date = $date ?? now();

        // Using PHP's built-in IntlDateFormatter for Hijri conversion
        if (class_exists('IntlDateFormatter')) {
            try {
                $formatter = new \IntlDateFormatter(
                    'en_SA@calendar=islamic-civil', // Use 'en' for Latin numerals
                    \IntlDateFormatter::FULL,
                    \IntlDateFormatter::FULL,
                    'Asia/Jakarta',
                    \IntlDateFormatter::TRADITIONAL,
                    'yyyy-MM-dd'
                );

                $hijriDateString = $formatter->format($date->timestamp);
                $parts = explode('-', $hijriDateString);

                return [
                    'year' => (int) $parts[0],
                    'month' => (int) $parts[1],
                    'day' => (int) $parts[2]
                ];
            } catch (\Exception $e) {
                // Fallback if formatter fails
            }
        }

        // Fallback: Using mathematical formula for Hijri conversion
        // Based on Umm al-Qura algorithm approximation
        $jd = cal_to_jd(CAL_GREGORIAN, $date->month, $date->day, $date->year);

        // Calculate Hijri date from Julian Day
        $l = $jd - 1948440 + 10632;
        $n = floor(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = (floor((10985 - $l) / 5316)) * (floor((50 * $l) / 17719)) + (floor($l / 5670)) * (floor((43 * $l) / 15238));
        $l = $l - (floor((30 - $j) / 15)) * (floor((17719 * $j) / 50)) - (floor($j / 16)) * (floor((15238 * $j) / 43)) + 29;
        $hijriMonth = floor((24 * $l) / 709);
        $hijriDay = $l - floor((709 * $hijriMonth) / 24);
        $hijriYear = 30 * $n + $j - 30;

        return [
            'year' => (int) $hijriYear,
            'month' => (int) $hijriMonth,
            'day' => (int) $hijriDay
        ];
    }

    /**
     * Generate unique leave number
     * Format: SIZYYYYYMMDDXXX (Hijri date, no separators)
     * Example: SIZ14470619001
     *
     * SIZ: Surat Izin
     * YYYY: Hijri Year (4 digits)
     * MM: Hijri Month (2 digits)
     * DD: Hijri Day (2 digits)
     * XXX: Sequential number (3 digits, reset monthly)
     */
    public static function generateLeaveNumber()
    {
        // Convert current date to Hijri
        $hijriDate = self::gregorianToHijri();
        $year = str_pad($hijriDate['year'], 4, '0', STR_PAD_LEFT);
        $month = str_pad($hijriDate['month'], 2, '0', STR_PAD_LEFT);
        $day = str_pad($hijriDate['day'], 2, '0', STR_PAD_LEFT);

        $prefix = "SIZ{$year}{$month}{$day}";

        // Get last leave number for current Hijri month
        $monthPrefix = "SIZ{$year}{$month}";
        $lastLeave = self::where('leave_number', 'LIKE', $monthPrefix . '%')
            ->orderByDesc('leave_number')
            ->first();

        if ($lastLeave) {
            // Extract number and increment (last 3 digits)
            $lastNumber = (int) substr($lastLeave->leave_number, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
