<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassScheduleDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function lessonHour()
    {
        return $this->belongsTo(LessonHour::class, 'lesson_hour_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function meetingSchedules()
    {
        return $this->hasMany(MeetingSchedule::class, 'class_schedule_detail_id', 'id');
    }

    public function getRepresentativeDetailId()
    {
        $academicYearId = $this->classSchedule?->academic_year_id;
        
        if (!$academicYearId) {
            return $this->id;
        }

        $representative = self::where('class_group_id', $this->class_group_id)
            ->where('study_id', $this->study_id)
            ->whereHas('classSchedule', function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            })
            ->orderBy('id', 'asc')
            ->first();

        return $representative ? $representative->id : $this->id;
    }
}
