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
}
