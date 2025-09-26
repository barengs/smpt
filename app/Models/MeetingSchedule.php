<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingSchedule extends Model
{
    protected $guarded = ['id'];

    public function schedule()
    {
        return $this->belongsTo(ClassScheduleDetail::class, 'class_schedule_detail_id', 'id');
    }
}
