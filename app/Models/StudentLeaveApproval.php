<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLeaveApproval extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function studentLeave()
    {
        return $this->belongsTo(StudentLeave::class, 'student_leave_id');
    }

    public function approver()
    {
        return $this->belongsTo(Staff::class, 'approver_id');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName()
    {
        $roles = [
            'keamanan' => 'Keamanan',
            'kepala_asrama' => 'Kepala Asrama',
            'wali_kelas' => 'Wali Kelas',
        ];

        return $roles[$this->approver_role] ?? $this->approver_role;
    }
}
