<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'email'                      => $this->email,
            'name'                       => $this->name,
            'role'                       => $this->getRoleNames()->first() ?? 'staf',
            'is_super_admin'             => $this->isSuperAdmin(),
            'accessible_institution_ids' => $this->getAccessibleInstitutionIds(),
            'accessible_program_ids'     => $this->getAccessibleProgramIds(),
        ];
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    public function parent()
    {
        return $this->hasOne(ParentProfile::class, 'user_id', 'id');
    }

    /**
     * Get menus accessible by the user based on their roles
     */
    public function getAccessibleMenus()
    {
        $roleIds = $this->roles()->pluck('id');

        return \App\Models\Menu::whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('roles.id', $roleIds);
        })->with('child', 'permissions')->get();
    }

    /**
     * True jika user adalah sysadmin (bypass semua scope data).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin')
            || $this->hasRole('sysadmin')
            || $this->roles()->where('category', 'super')->exists();
    }

    /**
     * IDs institusi yang dapat diakses. null = semua (sysadmin / staf pusat).
     */
    public function getAccessibleInstitutionIds(): ?array
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        $staff = $this->staff;
        if (! $staff) {
            return [];
        }

        $assignments = $staff->positionAssignments()->where('is_active', true)->with('position.organization')->get();

        // Jika ada jabatan di organisasi TANPA institusi -> Bypass (Akses Semua)
        if ($assignments->contains(fn($a) => $a->position && $a->position->organization && is_null($a->position->organization->educational_institution_id))) {
            return null;
        }

        // Dari Jabatan (Otomatis)
        $fromJob = $assignments->pluck('position.organization.educational_institution_id')->filter()->toArray();

        // Institusi via program yang ditugaskan (Manual fallback)
        $viaProgram = $staff->programs()
            ->with('institutions:id,program_id')
            ->get()
            ->pluck('institutions')
            ->flatten()
            ->pluck('id')
            ->toArray();

        // Institusi yang ditugaskan langsung (Manual fallback)
        $direct = $staff->educationalInstitutions()->pluck('educational_institutions.id')->toArray();

        return array_values(array_unique(array_merge($fromJob, $viaProgram, $direct)));
    }

    /**
     * IDs program yang dapat diakses. null = semua (sysadmin / staf pusat).
     */
    public function getAccessibleProgramIds(): ?array
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        $staff = $this->staff;
        if (! $staff) {
            return [];
        }

        $assignments = $staff->positionAssignments()->where('is_active', true)->with('position.organization.educationalInstitution')->get();

        if ($assignments->contains(fn($a) => $a->position && $a->position->organization && is_null($a->position->organization->educational_institution_id))) {
            return null;
        }

        $fromJob = $assignments->pluck('position.organization.educationalInstitution.program_id')->filter()->toArray();

        // Program langsung
        $direct = $staff->programs()->pluck('programs.id')->toArray();

        // Program dari institusi yang ditugaskan langsung
        $viaInst = $staff->educationalInstitutions()
            ->whereNotNull('program_id')
            ->pluck('program_id')
            ->toArray();

        return array_values(array_unique(array_merge($fromJob, $direct, $viaInst)));
    }
}
