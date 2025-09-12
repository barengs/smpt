<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $guarded = ['id'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function parents()
    {
        return $this->belongsTo(ParentProfile::class);
    }

    public function registration()
    {
        return $this->hasMany(Registration::class);
    }
}
