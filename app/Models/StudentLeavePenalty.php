<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLeavePenalty extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function studentLeave()
    {
        return $this->belongsTo(StudentLeave::class, 'student_leave_id');
    }

    public function studentLeaveReport()
    {
        return $this->belongsTo(StudentLeaveReport::class, 'student_leave_report_id');
    }

    public function sanction()
    {
        return $this->belongsTo(Sanction::class);
    }

    public function assignedByStaff()
    {
        return $this->belongsTo(Staff::class, 'assigned_by');
    }
}
