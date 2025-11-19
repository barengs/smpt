<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentViolation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'violation_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reporter()
    {
        return $this->belongsTo(Staff::class, 'reported_by');
    }

    public function sanctions()
    {
        return $this->hasMany(StudentSanction::class, 'student_violation_id');
    }
}
