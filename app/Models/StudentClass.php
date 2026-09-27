<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['education_id', 'class_id'];

    public function getEducationIdAttribute()
    {
        return $this->educational_institution_id;
    }

    public function getClassIdAttribute()
    {
        return $this->classroom_id;
    }

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
