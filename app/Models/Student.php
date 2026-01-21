<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $guarded = ['id'];

    protected $casts = [
        'born_at' => 'date',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function parents()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id', 'nik');
    }

    public function registration()
    {
        return $this->hasMany(Registration::class);
    }

    public function leaves()
    {
        return $this->hasMany(StudentLeave::class);
    }

    public function violations()
    {
        return $this->hasMany(StudentViolation::class);
    }

    public function activeRoom()
    {
        return $this->belongsToMany(Room::class, 'student_room_assignments')
                    ->wherePivot('is_active', true)
                    ->withPivot(['id', 'academic_year_id', 'start_date', 'end_date', 'is_active', 'notes', 'created_at', 'updated_at']);
    }
}
