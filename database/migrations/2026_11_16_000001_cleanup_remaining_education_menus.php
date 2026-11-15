<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        $obsoleteTitles = [
            'Manajemen Pendidikan',
            'Dashboard Pendidikan',
            'Jenjang',
            'Kelas',
            'Rombel',
            'Kelompok Pendidikan',
            'Kurikulum',
            'Dashboard Kurikulum',
            'Mata Pelajaran',
            'Jam Pelajaran',
            'Sesi Pelajaran',
            'Jadwal Pelajaran',
            'Guru',
            'Penugasan Guru',
            'Jam Mengajar',
            'Presensi',
            'Penilaian',
            'E-Raport',
            'Kenaikan Kelas',
            'Institusi Pendidikan',
            'Penempatan Kelas',
            'Statistik Presensi',
        ];

        $obsoleteRoutes = [
            '/dashboard/pendidikan/dashboard',
            '/dashboard/pendidikan/jenjang',
            '/dashboard/pendidikan/kelas',
            '/dashboard/pendidikan/rombel',
            '/dashboard/pendidikan/kelompok-pendidikan',
            '/dashboard/manajemen-kurikulum/dashboard',
            '/dashboard/manajemen-kurikulum/mata-pelajaran',
            '/dashboard/manajemen-kurikulum/jam-pelajaran',
            '/dashboard/manajemen-kurikulum/sesi-pelajaran',
            '/dashboard/manajemen-kurikulum/jadwal-pelajaran',
            '/dashboard/manajemen-kurikulum/guru',
            '/dashboard/manajemen-kurikulum/penugasan-guru',
            '/dashboard/manajemen-kurikulum/jam-mengajar',
            '/dashboard/manajemen-kurikulum/presensi',
            '/dashboard/manajemen-kurikulum/penilaian',
            '/dashboard/manajemen-kurikulum/raport',
            '/dashboard/manajemen-kurikulum/kenaikan-kelas',
            '/dashboard/manajemen-kurikulum/institusi-pendidikan',
            '/dashboard/manajemen-kurikulum/penempatan-kelas',
        ];

        $menuIds = DB::table('menus')
            ->whereIn('id_title', $obsoleteTitles)
            ->orWhereIn('route', $obsoleteRoutes)
            ->pluck('id')
            ->toArray();

        if (empty($menuIds)) {
            return;
        }

        // Include any remaining child menus under the matched parent menus
        $childIds = DB::table('menus')
            ->whereIn('parent_id', $menuIds)
            ->pluck('id')
            ->toArray();

        $allMenuIds = array_values(array_unique(array_merge($menuIds, $childIds)));

        if (Schema::hasTable('role_menu')) {
            DB::table('role_menu')->whereIn('menu_id', $allMenuIds)->delete();
        }

        DB::table('menus')->whereIn('id', $allMenuIds)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way cleanup: education management has migrated to Tarbiyah
    }
};
