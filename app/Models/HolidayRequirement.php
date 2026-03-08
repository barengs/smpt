<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HolidayPeriod;

class HolidayRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_period_id',
        'name',
        'description',
    ];

    public function period()
    {
        return $this->belongsTo(HolidayPeriod::class, 'holiday_period_id');
    }
}
