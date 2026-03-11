<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'score' => 'decimal:2',
        'date' => 'date',
    ];

    public function studentAssessment()
    {
        return $this->belongsTo(StudentAssessment::class);
    }
}
