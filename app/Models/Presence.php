<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $guarded = ['id'];

    public function students()
    {
        return $this->belongsToMany(Student::class);
    }

    public function meetingSchedules()
    {
        return $this->belongsToMany(MeetingSchedule::class, 'meeting_schedule_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
