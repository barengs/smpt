<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Http\Requests\ClassScheduleRequest;
use App\Http\Resources\ClassScheduleResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

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
                'classroom',
                'classGroup',
                'details.lessonHour',
                'details.teacher',
                'details.study'
            ])->get();

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

            // Check for schedule conflicts
            $conflict = $this->checkScheduleConflicts($request);
            if ($conflict) {
                return new ClassScheduleResource('Jadwal bentrok ditemukan', $conflict, 409);
            }

            // Create the schedule header
            $schedule = ClassSchedule::create($request->only([
                'academic_year_id',
                'education_id',
                'classroom_id',
                'class_group_id',
                'day',
                'session',
                'status'
            ]));

            // Create schedule details
            $details = [];
            foreach ($request->details as $detail) {
                $details[] = [
                    'class_schedule_id' => $schedule->id,
                    'lesson_hour_id' => $detail['lesson_hour_id'],
                    'teacher_id' => $detail['teacher_id'],
                    'study_id' => $detail['study_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            ClassScheduleDetail::insert($details);

            // Load relationships
            $schedule->load([
                'academicYear',
                'education',
                'classroom',
                'classGroup',
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
                'classroom',
                'classGroup',
                'details.lessonHour',
                'details.teacher',
                'details.study'
            ])->findOrFail($id);

            return new ClassScheduleResource('Data jadwal berhasil diambil', $schedule, 200);
        } catch (QueryException $e) {
            return new ClassScheduleResource('Terjadi kesalahan database saat mengambil data jadwal', ['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new ClassScheduleResource('Terjadi kesalahan saat mengambil data jadwal', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClassScheduleRequest $request, string $id)
    {
        try {
            $schedule = ClassSchedule::findOrFail($id);

            DB::beginTransaction();

            // Check for schedule conflicts (excluding current schedule)
            $conflict = $this->checkScheduleConflicts($request, $id);
            if ($conflict) {
                return new ClassScheduleResource('Jadwal bentrok ditemukan', $conflict, 409);
            }

            // Update the schedule header
            $schedule->update($request->only([
                'academic_year_id',
                'education_id',
                'classroom_id',
                'class_group_id',
                'day',
                'session',
                'status'
            ]));

            // Delete existing details
            ClassScheduleDetail::where('class_schedule_id', $schedule->id)->delete();

            // Create new schedule details
            $details = [];
            foreach ($request->details as $detail) {
                $details[] = [
                    'class_schedule_id' => $schedule->id,
                    'lesson_hour_id' => $detail['lesson_hour_id'],
                    'teacher_id' => $detail['teacher_id'],
                    'study_id' => $detail['study_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            ClassScheduleDetail::insert($details);

            // Load relationships
            $schedule->load([
                'academicYear',
                'education',
                'classroom',
                'classGroup',
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

            // Delete schedule details first
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
     * Check for schedule conflicts for a teacher
     *
     * @param ClassScheduleRequest $request
     * @param string|null $excludeScheduleId
     * @return array|null
     */
    private function checkScheduleConflicts(ClassScheduleRequest $request, $excludeScheduleId = null)
    {
        try {
            $conflicts = [];

            foreach ($request->details as $detail) {
                $teacherId = $detail['teacher_id'];
                $day = $request->day;
                $lessonHourId = $detail['lesson_hour_id'];

                // Check for conflicts with existing schedules
                $query = ClassScheduleDetail::where('teacher_id', $teacherId)
                    ->whereHas('classSchedule', function ($q) use ($day) {
                        $q->where('day', $day);
                    })
                    ->where('lesson_hour_id', $lessonHourId);

                // Exclude the current schedule if updating
                if ($excludeScheduleId) {
                    $query->where('class_schedule_id', '!=', $excludeScheduleId);
                }

                // Get conflicting schedules
                $conflictingDetails = $query->with('classSchedule')->get();

                foreach ($conflictingDetails as $conflictDetail) {
                    $conflicts[] = [
                        'teacher_id' => $teacherId,
                        'teacher_name' => $conflictDetail->teacher->first_name . ' ' . $conflictDetail->teacher->last_name,
                        'conflicting_schedule' => [
                            'id' => $conflictDetail->classSchedule->id,
                            'day' => $conflictDetail->classSchedule->day,
                            'session' => $conflictDetail->classSchedule->session,
                            'classroom' => $conflictDetail->classSchedule->classroom->name ?? null,
                            'class_group' => $conflictDetail->classSchedule->classGroup->name ?? null,
                        ],
                        'lesson_hour' => $conflictDetail->lessonHour->name ?? null,
                        'study' => $conflictDetail->study->name ?? null,
                    ];
                }
            }

            return !empty($conflicts) ? $conflicts : null;
        } catch (Exception $e) {
            // Log error but don't stop the process
            return null;
        }
    }
}
