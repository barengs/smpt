<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHolidayRequirementStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_holiday_check_id',
        'holiday_requirement_id',
        'is_met',
    ];

    public function check()
    {
        return $this->belongsTo(StudentHolidayCheck::class, 'student_holiday_check_id');
    }

    public function requirement()
    {
        return $this->belongsTo(HolidayRequirement::class, 'holiday_requirement_id');
    }
}
