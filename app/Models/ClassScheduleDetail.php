<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassScheduleDetail extends Model
{
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
}
