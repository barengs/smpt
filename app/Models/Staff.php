<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'first_name',
        'last_name',
        'nik',
        'nip',
        'email',
        'phone',
        'address',
        'zip_code',
        'photo',
        'marital_status',
        'village_id',
        'job_id',
        'status',
        'birth_date',
        'birth_place',
        'gender',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'birth_date' => 'date',
    ];

    /**
     * Get the user that owns the staff.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the full name of the staff.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function studies()
    {
        return $this->belongsToMany(Study::class, 'staff_studies', 'staff_id', 'study_id');
    }

    /**
     * Get the position assignments for the staff.
     */
    public function assignments()
    {
        return $this->hasMany(PositionAssignment::class, 'staff_id');
    }

    /**
     * Get the current position assignment for the staff.
     */
    public function currentPosition()
    {
        return $this->hasOne(PositionAssignment::class, 'staff_id')->where('is_active', true);
    }
}
