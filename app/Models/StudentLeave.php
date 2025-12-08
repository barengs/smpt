<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class StudentLeave extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'approved_at' => 'datetime',
        'has_penalty' => 'boolean',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function approver()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    public function report()
    {
        return $this->hasOne(StudentLeaveReport::class, 'student_leave_id');
    }

    public function penalties()
    {
        return $this->hasMany(StudentLeavePenalty::class, 'student_leave_id');
    }

    // Helper methods
    public function isOverdue()
    {
        if ($this->status === 'active' && $this->expected_return_date) {
            return Carbon::now()->isAfter($this->expected_return_date);
        }
        return false;
    }

    public function getDaysLate()
    {
        if ($this->expected_return_date) {
            $returnDate = $this->actual_return_date ?? Carbon::now();
            $expectedDate = Carbon::parse($this->expected_return_date);
            
            if ($returnDate->isAfter($expectedDate)) {
                return $returnDate->diffInDays($expectedDate);
            }
        }
        return 0;
    }

    public function canBeReported()
    {
        return in_array($this->status, ['approved', 'active', 'overdue']);
    }
}
