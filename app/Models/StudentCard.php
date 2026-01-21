<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'card_number',
        'is_active',
        'issued_at',
        'remarks',
        'validator',
        'validation_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'issued_at' => 'datetime',
        'validation_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
