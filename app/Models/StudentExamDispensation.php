<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExamDispensation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'commitment_date'  => 'date',
        'committed_amount' => 'decimal:2',
        'revoked_at'       => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicQuarter()
    {
        return $this->belongsTo(AcademicQuarter::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
