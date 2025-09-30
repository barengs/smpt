<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function students()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function academicYears()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function educations()
    {
        return $this->belongsTo(EducationalInstitution::class, 'educational_institution_id');
    }

    public function classrooms()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }

    public function approveBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
