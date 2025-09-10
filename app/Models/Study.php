<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Study extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'study_staff', 'study_id', 'staff_id');
    }
}
