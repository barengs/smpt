<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssessment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tugas_score' => 'decimal:2',
        'uh_score' => 'decimal:2',
        'uts_score' => 'decimal:2',
        'uas_score' => 'decimal:2',
        'praktik_score' => 'decimal:2',
        'proyek_score' => 'decimal:2',
        'portfolio_score' => 'decimal:2',
        'final_knowledge_score' => 'decimal:2',
        'final_skill_score' => 'decimal:2',
        'final_score' => 'decimal:2',
    ];

    public function classScheduleDetail()
    {
        return $this->belongsTo(ClassScheduleDetail::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function assessmentScores()
    {
        return $this->hasMany(AssessmentScore::class);
    }
}
