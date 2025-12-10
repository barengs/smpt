<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLeaveActivity extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function studentLeave()
    {
        return $this->belongsTo(StudentLeave::class, 'student_leave_id');
    }

    public function actor()
    {
        return $this->belongsTo(Staff::class, 'actor_id');
    }

    // Helper methods
    public function getActivityDescription()
    {
        $descriptions = [
            'created' => 'Dokumen izin dibuat',
            'submitted' => 'Dokumen diajukan untuk persetujuan',
            'approved_by_role' => 'Disetujui oleh ' . ($this->actor_role ? $this->getRoleDisplayName() : 'Staff'),
            'rejected_by_role' => 'Ditolak oleh ' . ($this->actor_role ? $this->getRoleDisplayName() : 'Staff'),
            'fully_approved' => 'Semua persetujuan terkumpul, izin disetujui',
            'fully_rejected' => 'Izin ditolak',
            'report_submitted' => 'Laporan kepulangan disubmit',
            'report_verified' => 'Laporan kepulangan diverifikasi',
            'penalty_assigned' => 'Penalti diberikan',
            'cancelled' => 'Izin dibatalkan',
            'updated' => 'Izin diupdate',
        ];

        return $descriptions[$this->activity_type] ?? $this->activity_type;
    }

    public function getRoleDisplayName()
    {
        $roles = [
            'keamanan' => 'Keamanan',
            'kepala_asrama' => 'Kepala Asrama',
            'wali_kelas' => 'Wali Kelas',
        ];

        return $roles[$this->actor_role] ?? $this->actor_role;
    }
}
