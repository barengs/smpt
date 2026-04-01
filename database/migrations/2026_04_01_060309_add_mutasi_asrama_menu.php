<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Menu;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah Menu Mutasi Asrama
        $manajemenSantri = Menu::where('id_title', 'Manajemen Santri')->first();
        if ($manajemenSantri) {
            $mutasiAsrama = Menu::firstOrCreate(
                ['route' => '/dashboard/santri/mutasi-asrama'],
                [
                    'id_title' => 'Mutasi Asrama',
                    'en_title' => 'Student Hostel Mutation',
                    'ar_title' => 'نقل الطلاب بين الغرف',
                    'description' => 'Manajemen pindah asrama dan kamar santri',
                    'icon' => 'arrow-right-left',
                    'parent_id' => $manajemenSantri->id,
                    'type' => 'submenu',
                    'position' => 'sidebar',
                    'status' => 'active',
                    'order' => 10,
                ]
            );

            // Berikan akses ke superadmin
            $superadmin = Role::where('name', 'superadmin')->first();
            if ($superadmin) {
                DB::table('role_menu')->updateOrInsert(
                    ['role_id' => $superadmin->id, 'menu_id' => $mutasiAsrama->id]
                );
            }
        }

        // 2. Pastikan Menu Laporan Pesantren dan Submenu-nya ada
        $laporanPesantren = Menu::where('id_title', 'Laporan Pesantren')->first();
        if (!$laporanPesantren) {
            $laporanPesantren = Menu::create([
                'id_title' => 'Laporan Pesantren',
                'en_title' => 'Pesantren Report',
                'ar_title' => 'تقرير المعهد',
                'description' => 'Laporan statistik dan aktivitas pesantren',
                'icon' => 'files',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 80,
            ]);
        }

        $reports = [
            [
                'id_title' => 'Statistik Santri',
                'route' => '/dashboard/kesantrian/laporan/statistik-santri',
                'icon' => 'pie-chart',
            ],
            [
                'id_title' => 'Laporan Pelanggaran',
                'route' => '/dashboard/kesantrian/laporan/pelanggaran',
                'icon' => 'alert-triangle',
            ],
            [
                'id_title' => 'Laporan Izin',
                'route' => '/dashboard/kesantrian/laporan/izin',
                'icon' => 'calendar',
            ],
            [
                'id_title' => 'Statistik Presensi',
                'route' => '/dashboard/kesantrian/laporan/presensi',
                'icon' => 'check-square',
            ],
        ];

        foreach ($reports as $index => $report) {
            $menuReport = Menu::firstOrCreate(
                ['route' => $report['route']],
                [
                    'id_title' => $report['id_title'],
                    'icon' => $report['icon'],
                    'parent_id' => $laporanPesantren->id,
                    'type' => 'submenu',
                    'position' => 'sidebar',
                    'status' => 'active',
                    'order' => ($index + 1) * 5,
                ]
            );

            // Berikan akses ke superadmin
            $superadmin = Role::where('name', 'superadmin')->first();
            if ($superadmin) {
                DB::table('role_menu')->updateOrInsert(
                    ['role_id' => $superadmin->id, 'menu_id' => $menuReport->id]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Menu::where('route', '/dashboard/santri/mutasi-asrama')->delete();
        // Laporan Pesantren dan isinya tidak dihapus untuk menghindari data loss jika ada custom order
    }
};
