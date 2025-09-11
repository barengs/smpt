<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlPanel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        // Ensure only one record exists
        static::creating(function ($model) {
            if (static::count() > 0) {
                throw new \Exception('Only one ControlPanel record is allowed.');
            }
        });
    }
}
