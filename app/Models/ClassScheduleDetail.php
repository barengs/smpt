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

    public function lessonHour()
    {
        return $this->belongsTo(LessonHour::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }
}
