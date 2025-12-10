<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLeaveReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'report_date' => 'date',
        'is_late' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function studentLeave()
    {
        return $this->belongsTo(StudentLeave::class, 'student_leave_id');
    }

    public function reportedToStaff()
    {
        return $this->belongsTo(Staff::class, 'reported_to');
    }

    public function verifiedByStaff()
    {
        return $this->belongsTo(Staff::class, 'verified_by');
    }

    public function penalties()
    {
        return $this->hasMany(StudentLeavePenalty::class, 'student_leave_report_id');
    }
}
