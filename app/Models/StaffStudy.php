<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffStudy extends Model
{
    protected $table = 'staff_studies';

    protected $guarded = ['id'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function study()
    {
        return $this->belongsTo(Study::class);
    }
}
