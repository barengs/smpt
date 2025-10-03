<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presence extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function meetingSchedule()
    {
        return $this->belongsTo(MeetingSchedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
