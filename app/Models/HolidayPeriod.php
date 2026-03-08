<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    public function requirements()
    {
        return $this->hasMany(HolidayRequirement::class);
    }

    public function studentChecks()
    {
        return $this->hasMany(StudentHolidayCheck::class);
    }
}
