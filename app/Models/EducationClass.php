<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'education_classes';

    protected $guarded = ['id'];

    public function education()
    {
        return $this->belongsToMany(Education::class, 'education_has_education_classes', 'education_class_id', 'education_id');
    }
}
