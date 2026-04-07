<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCardSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'front_template',
        'back_template',
        'guardian_front_template',
        'guardian_back_template',
        'staff_front_template',
        'staff_back_template',
        'authorized_official_id',
        'kop_surat',
        'stamp',
        'signature',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function authorizedOfficial()
    {
        return $this->belongsTo(Staff::class, 'authorized_official_id');
    }
}
