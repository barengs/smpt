<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHolidayCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_period_id',
        'student_id',
        'checkout_at',
        'checkin_at',
        'note',
    ];

    public function period()
    {
        return $this->belongsTo(HolidayPeriod::class, 'holiday_period_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function requirementStatuses()
    {
        return $this->hasMany(StudentHolidayRequirementStatus::class);
    }
}
