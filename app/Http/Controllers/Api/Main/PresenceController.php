<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\MeetingSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\ClassSchedule;
use App\Models\StudentClass;
use App\Http\Requests\PresenceRequest;
use App\Http\Resources\PresenceResource;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PresenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Check if we're filtering by class schedule
            if ($request->has('class_schedule_id')) {
                return $this->getPresenceByClassSchedule($request);
            }

            // Check if we're filtering by class schedule detail
            if ($request->has('class_schedule_detail_id')) {
                return $this->getPresenceByClassScheduleDetail($request);
            }

            // Check if we're filtering by meeting schedule
            if ($request->has('meeting_schedule_id')) {
                return $this->getPresenceByMeetingSchedule($request);
            }

            // Default presence listing
            $query = Presence::with(['student', 'meetingSchedule.schedule.classroom', 'meetingSchedule.schedule.classGroup', 'user']);

            // Filter by student if provided
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date if provided
            if ($request->has('date')) {
                $query->where('date', $request->date);
            }

            // Filter by user if provided
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $presences = $query->get();

            return new PresenceResource('Data presensi berhasil diambil', $presences, 200);
        } catch (QueryException $e) {
            Log::error('Database error while fetching presences: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat mengambil data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while fetching presences: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat mengambil data presensi', null, 500);
        }
    }

    /**
     * Get presence data by class schedule
     */
    private function getPresenceByClassSchedule(Request $request)
    {
        $classScheduleId = $request->class_schedule_id;

        // Get the class schedule with details
        $classSchedule = ClassSchedule::with([
            'academicYear',
            'education',
            'details.classroom',
            'details.classGroup',
            'details.lessonHour',
            'details.teacher',
            'details.study'
        ])->findOrFail($classScheduleId);

        // Add student data to each schedule detail
        $classSchedule->details->each(function ($detail) use ($classSchedule) {
            // Get students based on the same educational institution, academic year, classroom, and class group
            $students = StudentClass::with('students')
                ->where('educational_institution_id', $classSchedule->educational_institution_id)
                ->where('academic_year_id', $classSchedule->academic_year_id)
                ->where('classroom_id', $detail->classroom_id)
                ->where('class_group_id', $detail->class_group_id)
                ->where('approval_status', 'disetujui') // Only approved student classes
                ->get()
                ->pluck('students'); // Get only the student data

            // Add students to the detail
            $detail->students = $students;

            // Get meeting schedules for this detail
            $meetingSchedules = MeetingSchedule::where('class_schedule_detail_id', $detail->id)->get();

            // Add presence data for each meeting schedule
            $meetingSchedules->each(function ($meetingSchedule) {
                $meetingSchedule->presences = Presence::with(['student', 'user'])
                    ->where('meeting_schedule_id', $meetingSchedule->id)
                    ->get();
            });

            // Attach meeting schedules to the detail
            $detail->meeting_schedules = $meetingSchedules;
        });

        return new PresenceResource('Data presensi berdasarkan jadwal kelas berhasil diambil', $classSchedule, 200);
    }

    /**
     * Get presence data by class schedule detail
     */
    private function getPresenceByClassScheduleDetail(Request $request)
    {
        $classScheduleDetailId = $request->class_schedule_detail_id;

        // Get the class schedule detail with relationships
        $classScheduleDetail = ClassScheduleDetail::with([
            'classSchedule.academicYear',
            'classSchedule.education',
            'classroom',
            'classGroup',
            'lessonHour',
            'teacher',
            'study'
        ])->findOrFail($classScheduleDetailId);

        // Get students for this class schedule detail
        $students = StudentClass::with('students')
            ->where('educational_institution_id', $classScheduleDetail->classSchedule->educational_institution_id)
            ->where('academic_year_id', $classScheduleDetail->classSchedule->academic_year_id)
            ->where('classroom_id', $classScheduleDetail->classroom_id)
            ->where('class_group_id', $classScheduleDetail->class_group_id)
            ->where('approval_status', 'disetujui') // Only approved student classes
            ->get()
            ->pluck('students');

        // Add students to the detail
        $classScheduleDetail->students = $students;

        // Get meeting schedules for this detail
        $meetingSchedules = MeetingSchedule::where('class_schedule_detail_id', $classScheduleDetail->id)->get();

        // Add presence data for each meeting schedule
        $meetingSchedules->each(function ($meetingSchedule) {
            $meetingSchedule->presences = Presence::with(['student', 'user'])
                ->where('meeting_schedule_id', $meetingSchedule->id)
                ->get();
        });

        // Attach meeting schedules to the detail
        $classScheduleDetail->meeting_schedules = $meetingSchedules;

        return new PresenceResource('Data presensi berdasarkan detail jadwal kelas berhasil diambil', $classScheduleDetail, 200);
    }

    /**
     * Get presence data by meeting schedule
     */
    private function getPresenceByMeetingSchedule(Request $request)
    {
        $meetingScheduleId = $request->meeting_schedule_id;

        // Get the meeting schedule with relationships
        $meetingSchedule = MeetingSchedule::with([
            'schedule.classSchedule.academicYear',
            'schedule.classSchedule.education',
            'schedule.classroom',
            'schedule.classGroup',
            'schedule.lessonHour',
            'schedule.teacher',
            'schedule.study'
        ])->findOrFail($meetingScheduleId);

        // Get students for this meeting schedule's class
        $students = StudentClass::with('students')
            ->where('educational_institution_id', $meetingSchedule->schedule->classSchedule->educational_institution_id)
            ->where('academic_year_id', $meetingSchedule->schedule->classSchedule->academic_year_id)
            ->where('classroom_id', $meetingSchedule->schedule->classroom_id)
            ->where('class_group_id', $meetingSchedule->schedule->class_group_id)
            ->where('approval_status', 'disetujui') // Only approved student classes
            ->get()
            ->pluck('students');

        // Add students to the meeting schedule
        $meetingSchedule->students = $students;

        // Get presences for this meeting schedule
        $meetingSchedule->presences = Presence::with(['student', 'user'])
            ->where('meeting_schedule_id', $meetingSchedule->id)
            ->get();

        return new PresenceResource('Data presensi berdasarkan jadwal pertemuan berhasil diambil', $meetingSchedule, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PresenceRequest $request)
    {
        try {
            $presence = Presence::create($request->validated());

            // Load relationships
            $presence->load(['student', 'meetingSchedule', 'user']);

            return new PresenceResource('Data presensi berhasil disimpan', $presence, 201);
        } catch (ValidationException $e) {
            return new PresenceResource('Validasi gagal', $e->errors(), 422);
        } catch (QueryException $e) {
            Log::error('Database error while creating presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat menyimpan data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while creating presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat menyimpan data presensi', null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $presence = Presence::with(['student', 'meetingSchedule.schedule.classroom', 'meetingSchedule.schedule.classGroup', 'user'])->findOrFail($id);

            return new PresenceResource('Data presensi berhasil diambil', $presence, 200);
        } catch (ModelNotFoundException $e) {
            return new PresenceResource('Data presensi tidak ditemukan', null, 404);
        } catch (QueryException $e) {
            Log::error('Database error while fetching presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat mengambil data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while fetching presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat mengambil data presensi', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PresenceRequest $request, string $id)
    {
        try {
            $presence = Presence::findOrFail($id);

            $presence->update($request->validated());

            // Load relationships
            $presence->load(['student', 'meetingSchedule', 'user']);

            return new PresenceResource('Data presensi berhasil diperbarui', $presence, 200);
        } catch (ModelNotFoundException $e) {
            return new PresenceResource('Data presensi tidak ditemukan', null, 404);
        } catch (ValidationException $e) {
            return new PresenceResource('Validasi gagal', $e->errors(), 422);
        } catch (QueryException $e) {
            Log::error('Database error while updating presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat memperbarui data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while updating presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat memperbarui data presensi', null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $presence = Presence::findOrFail($id);
            $presence->delete();

            return new PresenceResource('Data presensi berhasil dihapus', null, 200);
        } catch (ModelNotFoundException $e) {
            return new PresenceResource('Data presensi tidak ditemukan', null, 404);
        } catch (QueryException $e) {
            Log::error('Database error while deleting presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat menghapus data presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while deleting presence: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat menghapus data presensi', null, 500);
        }
    }

    /**
     * Get presence statistics by status
     */
    public function statistics(Request $request)
    {
        try {
            $query = Presence::query();

            // Filter by student if provided
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            // Filter by meeting schedule if provided
            if ($request->has('meeting_schedule_id')) {
                $query->where('meeting_schedule_id', $request->meeting_schedule_id);
            }

            // Filter by date if provided
            if ($request->has('date')) {
                $query->where('date', $request->date);
            }

            // Filter by user if provided
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Get count for each status
            $hadir = (clone $query)->where('status', 'hadir')->count();
            $izin = (clone $query)->where('status', 'izin')->count();
            $sakit = (clone $query)->where('status', 'sakit')->count();
            $alpha = (clone $query)->where('status', 'alpha')->count();
            $total = $hadir + $izin + $sakit + $alpha;

            $statistics = [
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
                'total' => $total,
                'percentages' => [
                    'hadir' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0,
                    'izin' => $total > 0 ? round(($izin / $total) * 100, 2) : 0,
                    'sakit' => $total > 0 ? round(($sakit / $total) * 100, 2) : 0,
                    'alpha' => $total > 0 ? round(($alpha / $total) * 100, 2) : 0,
                ]
            ];

            return new PresenceResource('Statistik presensi berhasil diambil', $statistics, 200);
        } catch (QueryException $e) {
            Log::error('Database error while fetching presence statistics: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan database saat mengambil statistik presensi', null, 500);
        } catch (Exception $e) {
            Log::error('Error while fetching presence statistics: ' . $e->getMessage());
            return new PresenceResource('Terjadi kesalahan saat mengambil statistik presensi', null, 500);
        }
    }
}
