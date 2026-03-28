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
        'kop_surat',
        'stamp',
        'signature',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
