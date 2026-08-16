<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Staff;
use App\Models\Study;
use App\Models\Classroom;
use App\Models\ClassGroup;
use App\Models\ClassScheduleDetail;
use App\Models\Presence;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardKurikulumController extends Controller
{
    public function statistics(Request $request)
    {
        $today = Carbon::today();
        
        // 1. Tahun Ajaran Aktif
        $activeYear = AcademicYear::where('active', true)->first();
        
        // 2. Total Guru
        $totalTeachers = Staff::count(); // Adjust if there's a specific role for teacher
        
        // 3. Total Mata Pelajaran
        $totalSubjects = Study::count();
        
        // 4. Total Kelas & Rombel dengan jumlah siswa
        $rombelStats = ClassGroup::with('classroom')
            ->withCount('studentClasses as student_count')
            ->get()
            ->map(function($rombel) {
                return [
                    'id' => $rombel->id,
                    'name' => $rombel->name,
                    'tingkat' => $rombel->classroom ? $rombel->classroom->name : 'Unknown',
                    'student_count' => $rombel->student_count
                ];
            });
            
        $totalRombel = $rombelStats->count();
        $totalSiswaAktif = Student::where('status', 'Aktif')->count();
        
        // 5. Presensi Hari Ini (Matrix Persentase Presensi Aktif)
        // Hitung total record presensi hari ini
        $presenceToday = Presence::whereDate('created_at', $today)->get();
        $totalPresenceRecords = $presenceToday->count();
        
        $hadir = $presenceToday->where('status', 'Hadir')->count();
        $izin = $presenceToday->where('status', 'Izin')->count();
        $sakit = $presenceToday->where('status', 'Sakit')->count();
        $alpa = $presenceToday->where('status', 'Alpa')->count();
        
        // Asumsikan target presensi adalah total siswa aktif atau total jadwal
        // Jika belum ada presensi sama sekali hari ini
        $attendanceRate = 0;
        if ($totalPresenceRecords > 0) {
            $attendanceRate = round(($hadir / $totalPresenceRecords) * 100, 1);
        } else if ($totalSiswaAktif > 0) {
            // Bisa jadi presensi belum diisi, kita bisa set 0 atau null
            $attendanceRate = 0;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'academic_year' => $activeYear ? $activeYear->year : 'Tidak ada',
                'total_teachers' => $totalTeachers,
                'total_subjects' => $totalSubjects,
                'total_rombel' => $totalRombel,
                'total_students' => $totalSiswaAktif,
                'rombel_details' => $rombelStats,
                'attendance_today' => [
                    'rate' => $attendanceRate,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                    'total_recorded' => $totalPresenceRecords
                ]
            ]
        ]);
    }
}
