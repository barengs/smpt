<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Get the quarters for the academic year.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function academicQuarters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AcademicQuarter::class);
    }
}
