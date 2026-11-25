<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Drop educational / curriculum tables migrated to Tarbiyah
        $tablesToDrop = [
            'student_assessments',
            'assessment_scores',
            'assessment_formulas',
            'presences',
            'meeting_schedules',
            'class_schedule_details',
            'class_schedules',
            'staff_studies',
            'teacher_studies',
            'student_classes',
            'class_groups',
            'studies',
            'lesson_sessions',
            'lesson_hours',
            'classrooms',
        ];

        foreach ($tablesToDrop as $table) {
            Schema::dropIfExists($table);
        }

        // 2. Adjust menus by title (avoid hardcoded IDs which differ between dev & production)
        $dataMasterId = DB::table('menus')->where('id_title', 'Data Master')->value('id');
        $kepesantrenanId = DB::table('menus')->where('id_title', 'Kepesantrenan')->value('id');

        if ($dataMasterId) {
            DB::table('menus')->where('id_title', 'Program')->update([
                'parent_id' => $dataMasterId,
                'route'     => '/dashboard/master-data/program',
            ]);

            DB::table('menus')->where('id_title', 'Tahun Ajaran')->update([
                'parent_id' => $dataMasterId,
                'route'     => '/dashboard/master-data/tahun-ajaran',
            ]);
        }

        if ($kepesantrenanId) {
            DB::table('menus')->where('id_title', 'Jadwal Kegiatan')->update([
                'parent_id' => $kepesantrenanId,
                'route'     => '/dashboard/kepesantrenan/jadwal-kegiatan',
            ]);
        }

        DB::table('menus')->where('id_title', 'Asrama')->update([
            'route' => '/dashboard/kepesantrenan/asrama',
        ]);

        // 3. Delete obsolete education menus & their role_menu associations by title
        $obsoleteTitles = [
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
            'Manajemen Pendidikan',
            'Dashboard Pendidikan',
            'Jenjang',
            'Kelas',
            'Rombel',
            'Kelompok Pendidikan',
            'Penempatan Kelas',
            'Statistik Presensi',
        ];

        $menusToDelete = DB::table('menus')
            ->whereIn('id_title', $obsoleteTitles)
            ->pluck('id')
            ->toArray();

        if (!empty($menusToDelete)) {
            if (Schema::hasTable('role_menu')) {
                DB::table('role_menu')->whereIn('menu_id', $menusToDelete)->delete();
            }

            DB::table('menus')->whereIn('id', $menusToDelete)->delete();
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration as education has permanently migrated to Tarbiyah
    }
};

