<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\MeetingSchedule;
use App\Models\StudentClass;
use App\Http\Requests\ClassScheduleRequest;
use App\Http\Resources\ClassScheduleResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ClassScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $schedules = ClassSchedule::with([
                'academicYear',
                'education',
                'details.classroom',
                'details.classGroup',
                'details.lessonHour',
                'details.teacher',
                'details.study',
                'details.meetingSchedules'
            ])->get();

            // Add student data to each schedule
            $schedules = $this->addStudentDataToSchedules($schedules);

            return new ClassScheduleResource('Data jadwal berhasil diambil', $schedules, 200);
        } catch (QueryException $e) {
            return new ClassScheduleResource('Terjadi kesalahan database saat mengambil data jadwal', ['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new ClassScheduleResource('Terjadi kesalahan saat mengambil data jadwal', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClassScheduleRequest $request)
    {
        try {
            DB::beginTransaction();

            // Create the schedule header
            $schedule = ClassSchedule::create($request->only([
                'academic_year_id',
                'educational_institution_id',
                'session',
                'status'
            ]));

            // Create schedule details
            $details = [];
            $detailModels = [];
            foreach ($request->details as $detail) {
                // Check for schedule conflicts
                $conflict = $this->checkDetailScheduleConflicts($detail, $schedule->id);
                if ($conflict) {
                    DB::rollBack();
                    return new ClassScheduleResource('Jadwal bentrok ditemukan', $conflict, 409);
                }

                $detailData = [
                    'class_schedule_id' => $schedule->id,
                    'classroom_id' => $detail['classroom_id'],
                    'class_group_id' => $detail['class_group_id'],
                    'day' => $detail['day'],
                    'lesson_hour_id' => $detail['lesson_hour_id'],
                    'teacher_id' => $detail['teacher_id'],
                    'study_id' => $detail['study_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $detailModel = ClassScheduleDetail::create($detailData);
                $detailModels[] = $detailModel;

                // Create meeting schedules if meeting_count is provided
                if (isset($detail['meeting_count']) && $detail['meeting_count'] > 0) {
                    $this->createMeetingSchedules($detailModel, $detail['meeting_count'], $detail['day']);
                }
            }

            // Load relationships
            $schedule->load([
                'academicYear',
                'education',
                'details.classroom',
                'details.classGroup',
                'details.lessonHour',
                'details.teacher',
                'details.study'
            ]);

            DB::commit();

            return new ClassScheduleResource('Jadwal berhasil disimpan', $schedule, 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return new ClassScheduleResource('Validasi gagal', $e->errors(), 422);
        } catch (QueryException $e) {
            DB::rollBack();
            return new ClassScheduleResource('Terjadi kesalahan database saat menyimpan jadwal', ['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return new ClassScheduleResource('Terjadi kesalahan saat menyimpan jadwal', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $schedule = ClassSchedule::with([
                'academicYear',
                'education',
                'details.classroom',
                'details.classGroup',
                'details.lessonHour',
                'details.teacher',
                'details.study',
                'details.meetingSchedules'
            ])->findOrFail($id);

            // Add student data to the schedule
            $schedule = $this->addStudentDataToSchedule($schedule);

            return new ClassScheduleResource('Data jadwal berhasil diambil', $schedule, 200);
        } catch (QueryException $e) {
            return new ClassScheduleResource('Terjadi kesalahan database saat mengambil data jadwal', ['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new ClassScheduleResource('Terjadi kesalahan saat mengambil data jadwal', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add student data to schedule details
     *
     * @param ClassSchedule $schedule
     * @return ClassSchedule
     */
    private function addStudentDataToSchedule($schedule)
    {
        // For each detail in the schedule, add the related students
        $schedule->details->each(function ($detail) use ($schedule) {
            // Get students based on the same educational institution, academic year, classroom, and class group
            $students = StudentClass::with('students')
                ->where('educational_institution_id', $schedule->educational_institution_id)
                ->where('academic_year_id', $schedule->academic_year_id)
                ->where('classroom_id', $detail->classroom_id)
                ->where('class_group_id', $detail->class_group_id)
                ->where('approval_status', 'disetujui') // Only approved student classes
                ->get()
                ->pluck('students'); // Get only the student data

            // Add students to the detail
            $detail->students = $students;
        });

        return $schedule;
    }

    /**
     * Add student data to multiple schedules
     *
     * @param $schedules
     * @return mixed
     */
    private function addStudentDataToSchedules($schedules)
    {
        // For each schedule, add student data
        $schedules->each(function ($schedule) {
            $this->addStudentDataToSchedule($schedule);
        });

        return $schedules;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClassScheduleRequest $request, string $id)
    {
        try {
            $schedule = ClassSchedule::findOrFail($id);

            DB::beginTransaction();

            // Update the schedule header
            $schedule->update($request->only([
                'academic_year_id',
                'educational_institution_id',
                'session',
                'status'
            ]));

            // Delete existing details and their meeting schedules
            $existingDetails = ClassScheduleDetail::where('class_schedule_id', $schedule->id)->get();
            foreach ($existingDetails as $existingDetail) {
                // Delete meeting schedules first
                MeetingSchedule::where('class_schedule_detail_id', $existingDetail->id)->delete();
                // Delete the detail
                $existingDetail->delete();
            }

            // Create new schedule details
            $detailModels = [];
            foreach ($request->details as $detail) {
                // Check for schedule conflicts
                $conflict = $this->checkDetailScheduleConflicts($detail, $schedule->id);
                if ($conflict) {
                    DB::rollBack();
                    return new ClassScheduleResource('Jadwal bentrok ditemukan', $conflict, 409);
                }

                $detailData = [
                    'class_schedule_id' => $schedule->id,
                    'classroom_id' => $detail['classroom_id'],
                    'class_group_id' => $detail['class_group_id'],
                    'day' => $detail['day'],
                    'lesson_hour_id' => $detail['lesson_hour_id'],
                    'teacher_id' => $detail['teacher_id'],
                    'study_id' => $detail['study_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $detailModel = ClassScheduleDetail::create($detailData);
                $detailModels[] = $detailModel;

                // Create meeting schedules if meeting_count is provided
                if (isset($detail['meeting_count']) && $detail['meeting_count'] > 0) {
                    $this->createMeetingSchedules($detailModel, $detail['meeting_count'], $detail['day']);
                }
            }

            // Load relationships
            $schedule->load([
                'academicYear',
                'education',
                'details.classroom',
                'details.classGroup',
                'details.lessonHour',
                'details.teacher',
                'details.study'
            ]);

            DB::commit();

            return new ClassScheduleResource('Jadwal berhasil diperbarui', $schedule, 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return new ClassScheduleResource('Validasi gagal', $e->errors(), 422);
        } catch (QueryException $e) {
            DB::rollBack();
            return new ClassScheduleResource('Terjadi kesalahan database saat memperbarui jadwal', ['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return new ClassScheduleResource('Terjadi kesalahan saat memperbarui jadwal', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $schedule = ClassSchedule::findOrFail($id);

            DB::beginTransaction();

            // Delete meeting schedules first
            $details = ClassScheduleDetail::where('class_schedule_id', $schedule->id)->get();
            foreach ($details as $detail) {
                MeetingSchedule::where('class_schedule_detail_id', $detail->id)->delete();
            }

            // Delete schedule details
            ClassScheduleDetail::where('class_schedule_id', $schedule->id)->delete();

            // Delete the schedule
            $schedule->delete();

            DB::commit();

            return new ClassScheduleResource('Jadwal berhasil dihapus', null, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return new ClassScheduleResource('Terjadi kesalahan database saat menghapus jadwal', ['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return new ClassScheduleResource('Terjadi kesalahan saat menghapus jadwal', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check for schedule conflicts for a teacher in a detail
     *
     * @param array $detail
     * @param string $excludeScheduleId
     * @return array|null
     */
    private function checkDetailScheduleConflicts($detail, $excludeScheduleId = null)
    {
        try {
            $teacherId = $detail['teacher_id'];
            $day = $detail['day'];
            $lessonHourId = $detail['lesson_hour_id'];

            // Check for conflicts with existing schedules
            $query = ClassScheduleDetail::where('teacher_id', $teacherId)
                ->where('day', $day)
                ->where('lesson_hour_id', $lessonHourId);

            // Exclude the current schedule if updating
            if ($excludeScheduleId) {
                $query->where('class_schedule_id', '!=', $excludeScheduleId);
            }

            // Get conflicting schedules
            $conflictingDetails = $query->with(['classSchedule', 'classroom', 'classGroup'])->get();

            if ($conflictingDetails->count() > 0) {
                $conflicts = [];
                foreach ($conflictingDetails as $conflictDetail) {
                    $conflicts[] = [
                        'teacher_id' => $teacherId,
                        'teacher_name' => $conflictDetail->teacher->first_name . ' ' . $conflictDetail->teacher->last_name,
                        'conflicting_schedule' => [
                            'id' => $conflictDetail->classSchedule->id,
                            'day' => $conflictDetail->day,
                            'session' => $conflictDetail->classSchedule->session,
                            'classroom' => $conflictDetail->classroom->name ?? null,
                            'class_group' => $conflictDetail->classGroup->name ?? null,
                        ],
                        'lesson_hour' => $conflictDetail->lessonHour->name ?? null,
                        'study' => $conflictDetail->study->name ?? null,
                    ];
                }
                return $conflicts;
            }

            return null;
        } catch (Exception $e) {
            // Log error but don't stop the process
            return null;
        }
    }

    /**
     * Create meeting schedules for a class schedule detail
     *
     * @param ClassScheduleDetail $detail
     * @param int $meetingCount
     * @param string $day
     * @return void
     */
    private function createMeetingSchedules($detail, $meetingCount, $day)
    {
        try {
            // Get the next occurrence of the specified day
            $startDate = Carbon::now();
            $nextDate = $this->getNextDateForDay($startDate, $day);

            for ($i = 1; $i <= $meetingCount; $i++) {
                MeetingSchedule::create([
                    'class_schedule_detail_id' => $detail->id,
                    'meeting_sequence' => $i,
                    'meeting_date' => $nextDate->format('Y-m-d'),
                    'topic' => 'Pertemuan ' . $i,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Add 7 days for the next meeting
                $nextDate->addWeek();
            }
        } catch (Exception $e) {
            // Log error but don't stop the process
            Log::error('Error creating meeting schedules: ' . $e->getMessage());
        }
    }

    /**
     * Get the next date for a specific day of the week
     *
     * @param Carbon $startDate
     * @param string $day
     * @return Carbon
     */
    private function getNextDateForDay($startDate, $day)
    {
        $daysMap = [
            'senin' => Carbon::MONDAY,
            'selasa' => Carbon::TUESDAY,
            'rabu' => Carbon::WEDNESDAY,
            'kamis' => Carbon::THURSDAY,
            'jumat' => Carbon::FRIDAY,
            'sabtu' => Carbon::SATURDAY,
            'minggu' => Carbon::SUNDAY,
        ];

        $targetDay = $daysMap[strtolower($day)] ?? Carbon::MONDAY;
        $currentDay = $startDate->dayOfWeek;

        if ($currentDay == $targetDay) {
            // Today is the target day, so start from next week
            $daysToAdd = 7;
        } elseif ($currentDay < $targetDay) {
            // Target day is later this week
            $daysToAdd = $targetDay - $currentDay;
        } else {
            // Target day is next week
            $daysToAdd = (7 - $currentDay) + $targetDay;
        }

        return $startDate->copy()->addDays($daysToAdd);
    }

    /**
     * Export class schedule data to Excel (Readable)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ClassScheduleReadableExport, 'laporan_jadwal_pelajaran_' . date('Y-m-d_H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Backup class schedule data to CSV (Raw)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function backup()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ClassScheduleBackupExport, 'backup_jadwal_pelajaran_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
