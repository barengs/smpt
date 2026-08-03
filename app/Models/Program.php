<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function hostels()
    {
        return $this->hasMany(Hostel::class);
    }

    public function institutions()
    {
        return $this->hasMany(EducationalInstitution::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'staff_programs');
    }
}
