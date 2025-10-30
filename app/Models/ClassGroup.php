<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the staff member who is the class advisor (wali kelas) for this class group.
     */
    public function advisor()
    {
        return $this->belongsTo(Staff::class, 'advisor_id');
    }
}
