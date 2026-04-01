<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class MenuUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tambah Menu Mutasi Asrama
        $manajemenSantri = Menu::where('id_title', 'Manajemen Santri')->first();
        if ($manajemenSantri) {
            $mutasiAsrama = Menu::updateOrCreate(
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

            // 1b. Tambah Menu Penempatan Kelas
            $penempatanKelas = Menu::updateOrCreate(
                ['route' => '/dashboard/manajemen-kurikulum/penempatan-kelas'],
                [
                    'id_title' => 'Penempatan Kelas',
                    'en_title' => 'Class Placement',
                    'ar_title' => 'توزيع الفصول',
                    'description' => 'Penempatan santri baru ke dalam kelas',
                    'icon' => 'user-plus',
                    'parent_id' => $manajemenSantri->id ?? 7, // Fallback ke Kurikulum
                    'type' => 'submenu',
                    'position' => 'sidebar',
                    'status' => 'active',
                    'order' => 5,
                ]
            );

            // Berikan akses ke superadmin
            $superadmin = Role::where('name', 'superadmin')->first();
            if ($superadmin) {
                DB::table('role_menu')->updateOrInsert(
                    ['role_id' => $superadmin->id, 'menu_id' => $mutasiAsrama->id]
                );
                DB::table('role_menu')->updateOrInsert(
                    ['role_id' => $superadmin->id, 'menu_id' => $penempatanKelas->id]
                );
            }
        }

        // 2. Pastikan Menu Laporan Pesantren dan Submenu-nya ada
        $laporanPesantren = Menu::updateOrCreate(
            ['id_title' => 'Laporan Pesantren'],
            [
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
            ]
        );

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
            $menuReport = Menu::updateOrCreate(
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
}
