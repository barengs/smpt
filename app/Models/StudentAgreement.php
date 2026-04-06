<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentAgreement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'contract_agreed' => 'boolean',
        'compliance_agreed' => 'boolean',
        'urine_test_agreed' => 'boolean',
        'contract_agreed_at' => 'datetime',
        'compliance_agreed_at' => 'datetime',
        'urine_test_agreed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Generate unique document number
     * Format: AGR{YYYY}{MM}{DD}{XXX} (Hijri date)
     */
    public static function generateDocNumber()
    {
        $hijriDate = self::gregorianToHijri();
        $year = str_pad($hijriDate['year'], 4, '0', STR_PAD_LEFT);
        $month = str_pad($hijriDate['month'], 2, '0', STR_PAD_LEFT);
        $day = str_pad($hijriDate['day'], 2, '0', STR_PAD_LEFT);

        $prefix = "AGR{$year}{$month}{$day}";

        $lastAgreement = self::where('doc_number', 'LIKE', $prefix . '%')
            ->orderByDesc('doc_number')
            ->first();

        if ($lastAgreement) {
            $lastNumber = (int) substr($lastAgreement->doc_number, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Helper to convert Gregorian to Hijri (copied from StudentLeave)
     */
    private static function gregorianToHijri($date = null)
    {
        $date = $date ?? now();

        if (class_exists('IntlDateFormatter')) {
            try {
                $formatter = new \IntlDateFormatter(
                    'en_SA@calendar=islamic-civil',
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
                // Fallback
            }
        }

        $jd = cal_to_jd(CAL_GREGORIAN, (int)$date->month, (int)$date->day, (int)$date->year);
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
}
