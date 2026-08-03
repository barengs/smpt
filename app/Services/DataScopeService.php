<?php

namespace App\Services;

use App\Models\User;

class DataScopeService
{
    /**
     * IDs institusi yang dapat diakses user.
     * null = sysadmin, bypass semua filter.
     * [] = staff tanpa penugasan, tidak bisa lihat data apapun.
     */
    public static function getInstitutionIds(User $user): ?array
    {
        return $user->getAccessibleInstitutionIds();
    }

    /**
     * IDs program yang dapat diakses user.
     * null = sysadmin, bypass semua filter.
     * [] = staff tanpa penugasan.
     */
    public static function getProgramIds(User $user): ?array
    {
        return $user->getAccessibleProgramIds();
    }

    /**
     * True jika user bypass semua scope (sysadmin).
     */
    public static function isSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Terapkan scope program ke query.
     * Jika $programIds null = sysadmin, tidak filter.
     * Gunakan kolom 'program_id' langsung pada model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array|null  $programIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyProgramScope($query, ?array $programIds)
    {
        if ($programIds === null) {
            return $query; // bypass
        }

        if (empty($programIds)) {
            return $query->whereRaw('1 = 0'); // tidak ada akses
        }

        return $query->whereIn('program_id', $programIds);
    }

    /**
     * Terapkan scope institusi ke query.
     * Gunakan kolom 'educational_institution_id' langsung pada model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array|null  $institutionIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyInstitutionScope($query, ?array $institutionIds)
    {
        if ($institutionIds === null) {
            return $query; // bypass
        }

        if (empty($institutionIds)) {
            return $query->whereRaw('1 = 0'); // tidak ada akses
        }

        return $query->whereIn('educational_institution_id', $institutionIds);
    }

    /**
     * Scope student via relasi program (untuk model yang tidak punya program_id langsung,
     * melainkan punya relasi ke student).
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array|null  $programIds
     * @param  string  $studentRelation  nama relasi ke student (default: 'student')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyStudentProgramScope($query, ?array $programIds, string $studentRelation = 'student')
    {
        if ($programIds === null) {
            return $query;
        }

        if (empty($programIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas($studentRelation, function ($q) use ($programIds) {
            $q->whereIn('program_id', $programIds);
        });
    }
}
