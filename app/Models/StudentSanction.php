<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSanction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function studentViolation()
    {
        return $this->belongsTo(StudentViolation::class, 'student_violation_id');
    }

    public function sanction()
    {
        return $this->belongsTo(Sanction::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(Staff::class, 'assigned_by');
    }
}
