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

        // 2. Adjust menus: rescue Program, Tahun Ajaran, Jadwal Kegiatan, update Asrama route
        // Move Program (id 53) to Data Master (id 16)
        DB::table('menus')->where('id', 53)->update([
            'parent_id' => 16,
            'route'     => '/dashboard/master-data/program',
        ]);

        // Move Tahun Ajaran (id 54) to Data Master (id 16)
        DB::table('menus')->where('id', 54)->update([
            'parent_id' => 16,
            'route'     => '/dashboard/master-data/tahun-ajaran',
        ]);

        // Move Jadwal Kegiatan (id 59) to Kepesantrenan (id 12)
        DB::table('menus')->where('id', 59)->update([
            'parent_id' => 12,
            'route'     => '/dashboard/kepesantrenan/jadwal-kegiatan',
        ]);

        // Update Asrama (id 50) route
        DB::table('menus')->where('id', 50)->update([
            'route' => '/dashboard/kepesantrenan/asrama',
        ]);

        // 3. Delete obsolete education menus & their role_menu associations
        $menusToDelete = [
            9,   // Kurikulum (parent)
            26,  // Dashboard Kurikulum
            27,  // Mata Pelajaran
            28,  // Jam Pelajaran
            29,  // Sesi Pelajaran
            30,  // Jadwal Pelajaran
            31,  // Guru
            32,  // Penugasan Guru
            33,  // Jam Mengajar
            34,  // Presensi
            35,  // Penilaian
            36,  // E-Raport
            37,  // Kenaikan Kelas
            38,  // Institusi Pendidikan
            13,  // Manajemen Pendidikan (parent)
            52,  // Dashboard Pendidikan
            55,  // Jenjang
            56,  // Kelas
            57,  // Rombel
            58,  // Kelompok Pendidikan
            67,  // Penempatan Kelas (under Manajemen Santri)
            5,   // Statistik Presensi (under Laporan)
        ];

        if (Schema::hasTable('role_menu')) {
            DB::table('role_menu')->whereIn('menu_id', $menusToDelete)->delete();
        }

        DB::table('menus')->whereIn('id', $menusToDelete)->delete();

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

