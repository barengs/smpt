<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Core Permissions creation using firstOrCreate
        $permissions = [
            'lihat dashboard',
            'buat santri', 'lihat santri', 'ubah santri', 'hapus santri', 'aktivasi santri',
            'buat staf', 'lihat staf', 'ubah staf', 'hapus staf',
            'buat wali santri', 'lihat wali santri', 'ubah wali santri', 'hapus wali santri',
            'buat pendaftaran', 'lihat pendaftaran', 'ubah pendaftaran', 'hapus pendaftaran', 'aktivasi pendaftaran',
            'buat rekening', 'lihat rekening', 'ubah rekening', 'hapus rekening', 'aktivasi rekening',
            'buat transaksi', 'lihat transaksi', 'ubah transaksi', 'hapus transaksi', 'aktivasi transaksi',
            'buat produk', 'lihat produk', 'ubah produk', 'hapus produk',
            'buat coa', 'lihat coa', 'ubah coa', 'hapus coa',
            'buat jenis transaksi', 'lihat jenis transaksi', 'ubah jenis transaksi', 'hapus jenis transaksi',
            'buat laporan', 'lihat laporan', 'ubah laporan', 'hapus laporan',
            'buat berita', 'lihat berita', 'ubah berita', 'hapus berita',
            'buat tugas', 'lihat tugas', 'ubah tugas', 'hapus tugas',
            'buat institusi pendidikan', 'lihat institusi pendidikan', 'ubah institusi pendidikan', 'hapus institusi pendidikan',
            'buat program', 'lihat program', 'ubah program', 'hapus program',
            'buat tahun ajaran', 'lihat tahun ajaran', 'ubah tahun ajaran', 'hapus tahun ajaran',
            'buat asrama', 'lihat asrama', 'ubah asrama', 'hapus asrama',
            'buat jenjang pendidikan', 'lihat jenjang pendidikan', 'ubah jenjang pendidikan', 'hapus jenjang pendidikan',
            'buat kelas', 'lihat kelas', 'ubah kelas', 'hapus kelas',
            'buat rombel', 'lihat rombel', 'ubah rombel', 'hapus rombel',
            'buat kamar', 'lihat kamar', 'ubah kamar', 'hapus kamar',
            'buat mata pelajaran', 'lihat mata pelajaran', 'ubah mata pelajaran', 'hapus mata pelajaran',
            'buat jam pelajaran', 'lihat jam pelajaran', 'ubah jam pelajaran', 'hapus jam pelajaran',
            'buat guru', 'lihat guru', 'ubah guru', 'hapus guru',
            'buat penugasan guru', 'lihat penugasan guru', 'ubah penugasan guru', 'hapus penugasan guru',
            'buat presensi', 'lihat presensi', 'ubah presensi', 'hapus presensi',
            'buat pelanggaran', 'lihat pelanggaran', 'ubah pelanggaran', 'hapus pelanggaran',
            'buat perizinan', 'lihat perizinan', 'ubah perizinan', 'hapus perizinan',
            'buat libur', 'lihat libur', 'ubah libur', 'hapus libur',
            'buat pekerjaan', 'lihat pekerjaan', 'ubah pekerjaan', 'hapus pekerjaan',
            'buat peran', 'lihat peran', 'ubah peran', 'hapus peran',
            'buat izin', 'lihat izin', 'ubah izin', 'hapus izin',
            'buat navigasi', 'lihat navigasi', 'ubah navigasi', 'hapus navigasi',
            'buat profil aplikasi', 'lihat profil aplikasi', 'ubah profil aplikasi', 'hapus profil aplikasi',
            'buat topup', 'verifikasi topup', 'lihat verifikasi libur', 'lihat pengaturan kartu'
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }

        // 2. Role sync for superadmin
        $superadmin = Role::where('name', 'superadmin')->first();
        if ($superadmin) {
            $superadmin->syncPermissions(Permission::all());
        }

        // 3. Define Unified Menus (Parents and Submenus)
        $menusData = [
            // TOP LEVEL PARENTS
            'dasbor' => ['id_title' => 'Dasbor', 'en_title' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => '/dashboard', 'order' => 1],
            'bank_santri' => ['id_title' => 'Bank Santri', 'en_title' => 'Student Bank', 'icon' => 'landmark', 'route' => '#', 'order' => 10],
            'laporan_keuangan' => ['id_title' => 'Laporan Keuangan', 'en_title' => 'Finance Report', 'icon' => 'receipt', 'route' => '#', 'order' => 11],
            'manajemen_santri' => ['id_title' => 'Manajemen Santri', 'en_title' => 'Student Management', 'icon' => 'users', 'route' => '#', 'order' => 20],
            'staf' => ['id_title' => 'Manajemen Staf', 'en_title' => 'Staff Management', 'icon' => 'user-tie', 'route' => '#', 'order' => 25],
            'kurikulum' => ['id_title' => 'Kurikulum', 'en_title' => 'Curriculum', 'icon' => 'book', 'route' => '#', 'order' => 30],
            'kamtib' => ['id_title' => 'Manajemen Kamtib', 'en_title' => 'Security & Discipline', 'icon' => 'shield-check', 'route' => '#', 'order' => 40],
            'laporan_pesantren' => ['id_title' => 'Laporan Pesantren', 'en_title' => 'Pesantren Report', 'icon' => 'files', 'route' => '#', 'order' => 50],
            'master' => ['id_title' => 'Data Master', 'en_title' => 'Master Data', 'icon' => 'database', 'route' => '#', 'order' => 60],
            'pengaturan' => ['id_title' => 'Pengaturan', 'en_title' => 'Settings', 'icon' => 'settings', 'route' => '#', 'order' => 100],

            // BANK SANTRI SUBMENUS
            'bank_transaksi' => ['parent' => 'bank_santri', 'id_title' => 'Transaksi', 'route' => '/dashboard/bank-santri/transaksi', 'icon' => 'refresh-cw', 'order' => 1],
            'bank_paket' => ['parent' => 'bank_santri', 'id_title' => 'Paket Pembayaran', 'route' => '/dashboard/bank-santri/paket', 'icon' => 'package', 'order' => 2],
            'bank_pembayaran' => ['parent' => 'bank_santri', 'id_title' => 'Proses Pembayaran', 'route' => '/dashboard/bank-santri/pembayaran', 'icon' => 'credit-card', 'order' => 3],
            'bank_v_topup' => ['parent' => 'bank_santri', 'id_title' => 'Verifikasi Top-up', 'route' => '/dashboard/bank-santri/top-up/verifikasi', 'icon' => 'check-circle', 'order' => 4],
            'bank_rekening' => ['parent' => 'bank_santri', 'id_title' => 'Rekening Bank', 'route' => '/dashboard/bank-santri/rekening', 'icon' => 'users', 'order' => 5],
            'bank_koperasi' => ['parent' => 'bank_santri', 'id_title' => 'Kasir Koperasi', 'route' => '/dashboard/bank-santri/koperasi', 'icon' => 'shopping-cart', 'order' => 6],
            'bank_dash' => ['parent' => 'bank_santri', 'id_title' => 'Dashboard Bank', 'route' => '/dashboard/bank-santri/dashboard', 'icon' => 'pie-chart', 'order' => 7],
            'bank_cash' => ['parent' => 'bank_santri', 'id_title' => 'Top-Up / Setor Tunai', 'route' => '/dashboard/bank-santri/top-up/cash', 'icon' => 'wallet', 'order' => 8],
            'bank_transfer' => ['parent' => 'bank_santri', 'id_title' => 'Transfer Bank', 'route' => '/dashboard/bank-santri/top-up/transfer', 'icon' => 'smartphone', 'order' => 9],
            'bank_settings' => ['parent' => 'bank_santri', 'id_title' => 'Pengaturan Bank', 'route' => '/dashboard/bank-santri/settings', 'icon' => 'settings', 'order' => 10],

            // LAPORAN KEUANGAN SUBMENUS
            'keuangan_jurnal' => ['parent' => 'laporan_keuangan', 'id_title' => 'Jurnal Umum', 'route' => '/dashboard/bank-santri/laporan/jurnal', 'icon' => 'file-text', 'order' => 1],
            'keuangan_mutasi' => ['parent' => 'laporan_keuangan', 'id_title' => 'Mutasi Nasabah', 'route' => '/dashboard/bank-santri/laporan/mutasi', 'icon' => 'user-check', 'order' => 2],
            'keuangan_saldo' => ['parent' => 'laporan_keuangan', 'id_title' => 'Rekap Saldo', 'route' => '/dashboard/bank-santri/laporan/saldo', 'icon' => 'landmark', 'order' => 3],
            'keuangan_kasir' => ['parent' => 'laporan_keuangan', 'id_title' => 'Rekap Kasir', 'route' => '/dashboard/bank-santri/laporan/kasir', 'icon' => 'receipt', 'order' => 4],
            'keuangan_config' => ['parent' => 'laporan_keuangan', 'id_title' => 'Konfigurasi Transaksi', 'route' => '/dashboard/bank-santri/laporan/config', 'icon' => 'settings', 'order' => 5],

            // MANAJEMEN SANTRI SUBMENUS
            'santri_data' => ['parent' => 'manajemen_santri', 'id_title' => 'Data Santri', 'route' => '/dashboard/santri', 'icon' => 'user-graduate', 'order' => 1],
            'santri_reg' => ['parent' => 'manajemen_santri', 'id_title' => 'Pendaftaran Santri', 'route' => '/dashboard/pendaftaran-santri', 'icon' => 'user-plus', 'order' => 2],
            'santri_wali' => ['parent' => 'manajemen_santri', 'id_title' => 'Wali Santri', 'route' => '/dashboard/wali-santri-list', 'icon' => 'user-friends', 'order' => 3],
            'santri_mutasi' => ['parent' => 'manajemen_santri', 'id_title' => 'Mutasi Asrama', 'route' => '/dashboard/santri/mutasi-asrama', 'icon' => 'arrow-right-left', 'order' => 10],
            'santri_placement' => ['parent' => 'manajemen_santri', 'id_title' => 'Penempatan Kelas', 'route' => '/dashboard/manajemen-kurikulum/penempatan-kelas', 'icon' => 'user-plus', 'order' => 11],

            // KURIKULUM SUBMENUS
            'kurikulum_mapel' => ['parent' => 'kurikulum', 'id_title' => 'Mata Pelajaran', 'route' => '/dashboard/manajemen-kurikulum/mata-pelajaran', 'icon' => 'book-open', 'order' => 1],
            'kurikulum_jadwal' => ['parent' => 'kurikulum', 'id_title' => 'Jadwal Pelajaran', 'route' => '/dashboard/manajemen-kurikulum/jadwal-pelajaran', 'icon' => 'calendar-alt', 'order' => 2],
            'kurikulum_guru' => ['parent' => 'kurikulum', 'id_title' => 'Guru', 'route' => '/dashboard/manajemen-kurikulum/guru', 'icon' => 'user-tie', 'order' => 3],
            'kurikulum_penugasan' => ['parent' => 'kurikulum', 'id_title' => 'Penugasan Guru', 'route' => '/dashboard/manajemen-kurikulum/penugasan-guru', 'icon' => 'clipboard-list', 'order' => 4],
            'kurikulum_jam' => ['parent' => 'kurikulum', 'id_title' => 'Jam Mengajar', 'route' => '/dashboard/manajemen-kurikulum/jam-mengajar', 'icon' => 'clock', 'order' => 5],
            'kurikulum_presensi' => ['parent' => 'kurikulum', 'id_title' => 'Presensi', 'route' => '/dashboard/manajemen-kurikulum/presensi', 'icon' => 'check-circle', 'order' => 6],
            'kurikulum_promo' => ['parent' => 'kurikulum', 'id_title' => 'Kenaikan Kelas', 'route' => '/dashboard/manajemen-kurikulum/kenaikan-kelas', 'icon' => 'arrow-up', 'order' => 7],

            // KAMTIB SUBMENUS
            'kamtib_p' => ['parent' => 'kamtib', 'id_title' => 'Pelanggaran', 'route' => '/dashboard/manajemen-kamtib/pelanggaran', 'icon' => 'exclamation-triangle', 'order' => 1],
            'kamtib_iz' => ['parent' => 'kamtib', 'id_title' => 'Perizinan', 'route' => '/dashboard/manajemen-kamtib/perizinan', 'icon' => 'calendar-check', 'order' => 2],
            'kamtib_v_holiday' => ['parent' => 'kamtib', 'id_title' => 'Verifikasi Libur', 'route' => '/dashboard/manajemen-kamtib/libur-verifikasi', 'icon' => 'qr-code', 'order' => 10],

            // LAPORAN PESANTREN SUBMENUS
            'laporan_stats' => ['parent' => 'laporan_pesantren', 'id_title' => 'Statistik Santri', 'route' => '/dashboard/kesantrian/laporan/statistik-santri', 'icon' => 'pie-chart', 'order' => 1],
            'laporan_violation' => ['parent' => 'laporan_pesantren', 'id_title' => 'Laporan Pelanggaran', 'route' => '/dashboard/kesantrian/laporan/pelanggaran', 'icon' => 'alert-triangle', 'order' => 2],
            'laporan_leave' => ['parent' => 'laporan_pesantren', 'id_title' => 'Laporan Izin', 'route' => '/dashboard/kesantrian/laporan/izin', 'icon' => 'calendar', 'order' => 3],
            'laporan_presence' => ['parent' => 'laporan_pesantren', 'id_title' => 'Statistik Presensi', 'route' => '/dashboard/kesantrian/laporan/presensi', 'icon' => 'check-square', 'order' => 4],

            // PENGATURAN SUBMENUS
            'settings_card' => ['parent' => 'pengaturan', 'id_title' => 'Template Kartu Santri', 'route' => '/dashboard/settings/student-card-template', 'icon' => 'credit-card', 'order' => 10],
        ];

        // Store created menus to easily link parents
        $createdMenus = [];

        // First pass: Create main parents
        foreach ($menusData as $key => $data) {
            if (!isset($data['parent'])) {
                $createdMenus[$key] = Menu::updateOrCreate(
                    ['id_title' => $data['id_title']],
                    [
                        'en_title' => $data['en_title'] ?? $data['id_title'],
                        'icon' => $data['icon'],
                        'route' => $data['route'],
                        'parent_id' => null,
                        'type' => 'main',
                        'position' => 'sidebar',
                        'status' => 'active',
                        'order' => $data['order'],
                    ]
                );
            }
        }

        // Second pass: Create submenus linked to parents
        foreach ($menusData as $key => $data) {
            if (isset($data['parent'])) {
                $parentId = $createdMenus[$data['parent']]->id ?? null;
                $menu = Menu::updateOrCreate(
                    ['route' => $data['route']],
                    [
                        'id_title' => $data['id_title'],
                        'en_title' => $data['en_title'] ?? $data['id_title'],
                        'icon' => $data['icon'],
                        'parent_id' => $parentId,
                        'type' => 'submenu',
                        'position' => 'sidebar',
                        'status' => 'active',
                        'order' => $data['order'],
                    ]
                );
            }
        }

        // Sync superadmin role menu access
        if ($superadmin) {
            $allMenus = Menu::all();
            foreach ($allMenus as $m) {
                DB::table('role_menu')->updateOrInsert(
                    ['role_id' => $superadmin->id, 'menu_id' => $m->id]
                );
            }
        }
    }
}
