<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $guarded = ['id'];


    public function parent()
    {
        return $this->belongsTo(ParentProfile::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function files()
    {
        return $this->hasMany(RegistrationFile::class);
    }
}
