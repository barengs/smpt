<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionAssignment extends Model
{
    use HasFactory;

    protected static function booted()
    {
        $syncInstitutions = function ($assignment) {
            if ($assignment->staff_id && $assignment->is_active) {
                // Ensure relations are loaded or accessible
                $assignment->loadMissing('position.organization');
                if ($assignment->position && $assignment->position->organization && $assignment->position->organization->educational_institution_id) {
                    $staff = Staff::find($assignment->staff_id);
                    if ($staff) {
                        $staff->educationalInstitutions()->syncWithoutDetaching([
                            $assignment->position->organization->educational_institution_id
                        ]);
                    }
                }
            }
        };

        static::created($syncInstitutions);
        static::updated($syncInstitutions);
    }

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
        return $this->belongsTo(Position::class);
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
