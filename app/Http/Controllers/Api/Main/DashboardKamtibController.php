<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentViolation;
use App\Models\StudentLeave;
use App\Models\HolidayPeriod;
use App\Models\StudentHolidayCheck;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardKamtibController extends Controller
{
    public function statistics(Request $request)
    {
        $today = Carbon::today();
        
        // 1. Izin Aktif Hari Ini
        $activeLeaves = StudentLeave::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNull('actual_return_date')
            ->count();
            
        // 2. Pelanggaran Hari Ini
        $violationsToday = StudentViolation::whereDate('created_at', $today)->count();
        
        // 3. Liburan Aktif
        $activeHoliday = HolidayPeriod::where('status', 'active')
            ->orWhere(function($query) use ($today) {
                $query->whereDate('start_date', '<=', $today)
                      ->whereDate('end_date', '>=', $today);
            })
            ->latest()
            ->first();
            
        $holidayStats = null;
        if ($activeHoliday) {
            $checks = StudentHolidayCheck::with(['student.program', 'student.hostel'])
                ->where('holiday_period_id', $activeHoliday->id)
                ->get();
                
            $totalAssigned = $checks->count();
            $totalCheckout = $checks->whereNotNull('checkout_at')->count();
            $totalCheckin = $checks->whereNotNull('checkin_at')->count();

            $byProgram = $checks->groupBy(function($check) {
                return $check->student?->program?->name ?? 'Tanpa Program';
            })->map(function($group, $key) {
                $checkout = $group->whereNotNull('checkout_at')->count();
                $checkin = $group->whereNotNull('checkin_at')->count();
                return [
                    'name' => $key,
                    'total' => $group->count(),
                    'checkout_count' => $checkout,
                    'checkin_count' => $checkin,
                    'not_returned_count' => $checkout - $checkin,
                ];
            })->values();

            $byAsrama = $checks->groupBy(function($check) {
                return $check->student?->hostel?->name ?? 'Tanpa Asrama';
            })->map(function($group, $key) {
                $checkout = $group->whereNotNull('checkout_at')->count();
                $checkin = $group->whereNotNull('checkin_at')->count();
                return [
                    'name' => $key,
                    'total' => $group->count(),
                    'checkout_count' => $checkout,
                    'checkin_count' => $checkin,
                    'not_returned_count' => $checkout - $checkin,
                ];
            })->values();
                
            $holidayStats = [
                'title' => $activeHoliday->name,
                'start_date' => $activeHoliday->start_date,
                'end_date' => $activeHoliday->end_date,
                'total_santri' => $totalAssigned,
                'checkout_count' => $totalCheckout,
                'checkin_count' => $totalCheckin,
                'not_returned_count' => $totalCheckout - $totalCheckin,
                'by_program' => $byProgram,
                'by_asrama' => $byAsrama,
            ];
        }
        
        // 4. Tren Pelanggaran 6 Bulan Terakhir
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $violationTrends = StudentViolation::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'name' => Carbon::createFromDate($item->year, $item->month, 1)->translatedFormat('M Y'),
                    'total' => $item->total
                ];
            });
            
        // 5. Tren Perizinan 6 Bulan Terakhir
        $leaveTrends = StudentLeave::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'name' => Carbon::createFromDate($item->year, $item->month, 1)->translatedFormat('M Y'),
                    'total' => $item->total
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'active_leaves' => $activeLeaves,
                'violations_today' => $violationsToday,
                'holiday' => $holidayStats,
                'trends' => [
                    'violations' => $violationTrends,
                    'leaves' => $leaveTrends,
                ]
            ]
        ]);
    }

    public function holidayStudents(Request $request)
    {
        $status = $request->query('status'); // checkout, checkin, not_returned
        $today = Carbon::today();
        
        $activeHoliday = HolidayPeriod::where('status', 'active')
            ->orWhere(function($query) use ($today) {
                $query->whereDate('start_date', '<=', $today)
                      ->whereDate('end_date', '>=', $today);
            })
            ->latest()
            ->first();

        if (!$activeHoliday) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $query = StudentHolidayCheck::with(['student.hostel'])
            ->where('holiday_period_id', $activeHoliday->id);

        if ($status === 'checkout') {
            $query->whereNotNull('checkout_at');
        } elseif ($status === 'checkin') {
            $query->whereNotNull('checkin_at');
        } elseif ($status === 'not_returned') {
            $query->whereNotNull('checkout_at')
                  ->whereNull('checkin_at');
        }

        $checks = $query->get();

        $students = $checks->map(function($check) {
            $student = $check->student;
            if (!$student) return null;
            return [
                'id' => $student->id,
                'nis' => $student->nis,
                'full_name' => trim($student->first_name . ' ' . $student->last_name),
                'room_name' => $student->hostel ? $student->hostel->name : '-',
                'checkout_at' => $check->checkout_at,
                'checkin_at' => $check->checkin_at
            ];
        })->filter()->values();

        return response()->json([
            'status' => 'success',
            'data' => $students
        ]);
    }
}
