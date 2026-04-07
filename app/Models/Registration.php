<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $guarded = ['id'];


    public function parent()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id', 'nik');
    }

    public function village()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class, 'village_id', 'code');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(RegistrationFile::class);
    }
}
