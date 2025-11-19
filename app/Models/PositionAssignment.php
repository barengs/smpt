<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionAssignment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class)->nullable();
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    // Keep the official method for backward compatibility but alias it to staff
    public function official()
    {
        return $this->staff();
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }
}
