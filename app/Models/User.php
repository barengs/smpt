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
     * Helper recursive to find educational_institution_id from organization tree
     */
    private function resolveInstitutionId($org)
    {
        if (!$org) return null;
        if (!empty($org->educational_institution_id)) return $org->educational_institution_id;
        // Lazy load parent if not loaded
        if (!$org->relationLoaded('parent')) $org->load('parent');
        if ($org->parent) return $this->resolveInstitutionId($org->parent);
        return null;
    }

    /**
     * Helper recursive to find program_id from organization tree
     */
    private function resolveProgramId($org)
    {
        if (!$org) return null;
        if (!empty($org->program_id)) return $org->program_id;
        // Lazy load parent if not loaded
        if (!$org->relationLoaded('parent')) $org->load('parent');
        if ($org->parent) return $this->resolveProgramId($org->parent);
        return null;
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

        // 1. Resolve institution IDs for all active assignments
        $fromInstJob = [];
        $isPusat = false;

        foreach ($assignments as $a) {
            $org = $a->position ? $a->position->organization : null;
            if (!$org) continue;

            $instId = $this->resolveInstitutionId($org);
            $progId = $this->resolveProgramId($org);

            if (is_null($instId) && is_null($progId)) {
                $isPusat = true; // No inst and no program -> level pusat
                break;
            }

            if ($instId) $fromInstJob[] = $instId;
        }

        if ($isPusat) {
            return null;
        }

        return array_values(array_unique($fromInstJob));
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

        $assignments = $staff->assignments()->where('is_active', true)->with('position.organization')->get();

        $fromProgJob = [];
        $isPusat = false;

        foreach ($assignments as $a) {
            $org = $a->position ? $a->position->organization : null;
            if (!$org) continue;

            $instId = $this->resolveInstitutionId($org);
            $progId = $this->resolveProgramId($org);

            if (is_null($instId) && is_null($progId)) {
                $isPusat = true;
                break;
            }

            if ($progId) {
                $fromProgJob[] = $progId;
            }
        }

        if ($isPusat) {
            return null;
        }

        // Program langsung dari penugasan staff_programs
        $direct = $staff->programs()->pluck('programs.id')->toArray();

        return array_values(array_unique(array_merge($fromProgJob, $direct)));
    }
}
