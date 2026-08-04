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

        $assignments = $staff->assignments()->where('is_active', true)->with('position.organization')->get();

        // 1. Level Pusat (Tanpa Institusi & Tanpa Program) -> Bypass (Akses Semua)
        if ($assignments->contains(fn($a) => $a->position && $a->position->organization && is_null($a->position->organization->educational_institution_id) && is_null($a->position->organization->program_id))) {
            return null;
        }

        // 2. Level Institusi
        $fromInstJob = $assignments->pluck('position.organization.educational_institution_id')->filter()->toArray();

        // 3. Level Program (Bisa lihat semua institusi di bawah programnya)
        $programIdsFromJob = $assignments->pluck('position.organization.program_id')->filter()->toArray();
        $fromProgJobInsts = \App\Models\EducationalInstitution::whereIn('program_id', $programIdsFromJob)->pluck('id')->toArray();

        $fromJob = array_merge($fromInstJob, $fromProgJobInsts);

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

        $assignments = $staff->assignments()->where('is_active', true)->with('position.organization.educationalInstitution')->get();

        if ($assignments->contains(fn($a) => $a->position && $a->position->organization && is_null($a->position->organization->educational_institution_id) && is_null($a->position->organization->program_id))) {
            return null;
        }

        // Program dari institusi jabatan
        $fromInstJob = $assignments->pluck('position.organization.educationalInstitution.program_id')->filter()->toArray();

        // Program dari organisasi level program
        $fromProgJob = $assignments->pluck('position.organization.program_id')->filter()->toArray();

        $fromJob = array_merge($fromInstJob, $fromProgJob);

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
