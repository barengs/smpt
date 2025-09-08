<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Education extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'educations';

    protected $guarded = ['id'];


    public function education_class ()
    {
        return $this->belongsToMany(EducationClass::class, 'education_has_education_classes', 'education_id', 'education_class_id');
    }
}
