<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Education;
use App\Models\EducationClass;
use App\Models\Staff;

class EducationalInstitution extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the education that owns the educational institution.
     */
    public function education()
    {
        return $this->belongsTo(Education::class, 'education_id');
    }

    /**
     * Get the education class that owns the educational institution.
     */
    public function educationClass()
    {
        return $this->belongsTo(EducationClass::class, 'education_class_id');
    }

    /**
     * Get the headmaster that owns the educational institution.
     */
    public function headmaster()
    {
        return $this->belongsTo(Staff::class, 'headmaster_id');
    }
}
