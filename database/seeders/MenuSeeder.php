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
            'buat topup', 'verifikasi topup', 'lihat verifikasi libur', 'lihat pengaturan kartu',
            'lihat statistik santri', 'lihat laporan kamtib', 'lihat penanggung jawab magang', 'lihat institusi tugas',
            'buat pemetaan coa', 'lihat pemetaan coa'
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }

        // 2. Role sync for superadmin
        $superadmin = Role::where('name', 'superadmin')->first();
        if ($superadmin) {
            $superadmin->syncPermissions(Permission::all());
        }

        // 3. Define Unified Menus (Parents and Submenus) aligned with "SIAP" Layout
        // USING id_title AS THE UNIQUE KEY FORupdateOrCreate
        $menusData = [
            // TOP LEVEL PARENTS
            'Dasbor' => ['en_title' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => '/dashboard', 'order' => 1],
            'Guru Tugas' => ['en_title' => 'Assignment Teacher', 'icon' => 'chalkboard-teacher', 'route' => '#', 'order' => 5],
            'Manajemen Staf' => ['en_title' => 'Staff Management', 'icon' => 'user-tie', 'route' => '#', 'order' => 10],
            'Kurikulum' => ['en_title' => 'Curriculum', 'icon' => 'book', 'route' => '#', 'order' => 15],
            'Bank Santri' => ['en_title' => 'Student Bank', 'icon' => 'landmark', 'route' => '#', 'order' => 20],
            'Laporan Keuangan' => ['en_title' => 'Finance Report Center', 'icon' => 'receipt', 'route' => '#', 'order' => 25],
            'Manajemen Bank' => ['en_title' => 'Bank Management', 'icon' => 'briefcase', 'route' => '#', 'order' => 30],
            'Manajemen Kamtib' => ['en_title' => 'Security & Discipline', 'icon' => 'shield-check', 'route' => '#', 'order' => 35],
            'Kepesantrenan' => ['en_title' => 'Islamic Boarding School', 'icon' => 'mosque', 'route' => '#', 'order' => 40],
            'Manajemen Pendidikan' => ['en_title' => 'Education Management', 'icon' => 'graduation-cap', 'route' => '#', 'order' => 45],
            'Informasi' => ['en_title' => 'Information', 'icon' => 'info-circle', 'route' => '#', 'order' => 50],
            'Manajemen Santri' => ['en_title' => 'Student Management', 'icon' => 'users', 'route' => '#', 'order' => 55],
            'Data Master' => ['en_title' => 'Master Data', 'icon' => 'database', 'route' => '#', 'order' => 60],
            'Pengaturan' => ['en_title' => 'Settings', 'icon' => 'settings', 'route' => '#', 'order' => 100],
            'Laporan Pesantren' => ['en_title' => 'Pesantren Report', 'icon' => 'files', 'route' => '#', 'order' => 110],

            // 1. GURU TUGAS SUBMENUS (Used distinctive names to avoid collisions)
            'Daftar Guru Tugas' => ['parent' => 'Guru Tugas', 'en_title' => 'Teacher Assignment List', 'route' => '/dashboard/guru-tugas', 'icon' => 'users', 'order' => 1],
            'Penanggung Jawab' => ['parent' => 'Guru Tugas', 'en_title' => 'Responsible Person', 'route' => '/dashboard/penanggung-jawab-magang', 'icon' => 'user-check', 'order' => 2],
            'Institusi Tugas' => ['parent' => 'Guru Tugas', 'en_title' => 'Duty Institution', 'route' => '/dashboard/institusi-tugas', 'icon' => 'building', 'order' => 3],

            // 2. STAF SUBMENUS
            'Data Staf' => ['parent' => 'Manajemen Staf', 'en_title' => 'Staff Data', 'route' => '/dashboard/staf', 'icon' => 'users', 'order' => 1],
            'Struktur Organisasi' => ['parent' => 'Manajemen Staf', 'en_title' => 'Organizational Structure', 'route' => '/dashboard/organisasi', 'icon' => 'git-branch', 'order' => 2],

            // 3. KURIKULUM SUBMENUS
            'Mata Pelajaran' => ['parent' => 'Kurikulum', 'en_title' => 'Subject', 'route' => '/dashboard/manajemen-kurikulum/mata-pelajaran', 'icon' => 'book-open', 'order' => 1],
            'Jadwal Pelajaran' => ['parent' => 'Kurikulum', 'en_title' => 'Class Schedule', 'route' => '/dashboard/manajemen-kurikulum/jadwal-pelajaran', 'icon' => 'calendar-alt', 'order' => 2],
            'Guru' => ['parent' => 'Kurikulum', 'en_title' => 'Teacher', 'route' => '/dashboard/manajemen-kurikulum/guru', 'icon' => 'user-tie', 'order' => 3],
            'Penugasan Guru' => ['parent' => 'Kurikulum', 'en_title' => 'Teacher Assignment', 'route' => '/dashboard/manajemen-kurikulum/penugasan-guru', 'icon' => 'clipboard-list', 'order' => 4],
            'Jam Mengajar' => ['parent' => 'Kurikulum', 'en_title' => 'Teaching Hour', 'route' => '/dashboard/manajemen-kurikulum/jam-mengajar', 'icon' => 'clock', 'order' => 5],
            'Presensi' => ['parent' => 'Kurikulum', 'en_title' => 'Presence', 'route' => '/dashboard/manajemen-kurikulum/presensi', 'icon' => 'check-circle', 'order' => 6],
            'Penilaian' => ['parent' => 'Kurikulum', 'en_title' => 'Assessment', 'route' => '/dashboard/manajemen-kurikulum/penilaian', 'icon' => 'star', 'order' => 7],
            'E-Raport' => ['parent' => 'Kurikulum', 'en_title' => 'E-Report', 'route' => '/dashboard/manajemen-kurikulum/raport', 'icon' => 'file-text', 'order' => 8],
            'Kenaikan Kelas' => ['parent' => 'Kurikulum', 'en_title' => 'Class Promotion', 'route' => '/dashboard/manajemen-kurikulum/kenaikan-kelas', 'icon' => 'arrow-up', 'order' => 9],
            'Institusi Pendidikan' => ['parent' => 'Kurikulum', 'en_title' => 'Educational Institution', 'route' => '/dashboard/manajemen-kurikulum/institusi-pendidikan', 'icon' => 'school', 'order' => 10],

            // 4. BANK SANTRI SUBMENUS
            'Dashboard Bank' => ['parent' => 'Bank Santri', 'en_title' => 'Bank Dashboard', 'route' => '/dashboard/bank-santri/dashboard', 'icon' => 'pie-chart', 'order' => 1],
            'Transaksi Bank' => ['parent' => 'Bank Santri', 'en_title' => 'Bank Transaction', 'route' => '/dashboard/bank-santri/transaksi', 'icon' => 'refresh-cw', 'order' => 2],
            'Paket Pembayaran' => ['parent' => 'Bank Santri', 'en_title' => 'Payment Package', 'route' => '/dashboard/bank-santri/paket', 'icon' => 'package', 'order' => 3],
            'Proses Pembayaran' => ['parent' => 'Bank Santri', 'en_title' => 'Payment Process', 'route' => '/dashboard/bank-santri/pembayaran', 'icon' => 'credit-card', 'order' => 4],
            'Verifikasi Top-up' => ['parent' => 'Bank Santri', 'en_title' => 'Top-up Verification', 'route' => '/dashboard/bank-santri/top-up/verifikasi', 'icon' => 'check-circle', 'order' => 5],
            'Rekening Bank' => ['parent' => 'Bank Santri', 'en_title' => 'Bank Account', 'route' => '/dashboard/bank-santri/rekening', 'icon' => 'landmark', 'order' => 6],
            'Kasir Koperasi' => ['parent' => 'Bank Santri', 'en_title' => 'Cooperative Cashier', 'route' => '/dashboard/bank-santri/koperasi', 'icon' => 'shopping-cart', 'order' => 7],
            'Top-Up / Setor Tunai' => ['parent' => 'Bank Santri', 'en_title' => 'Top-up / Cash Deposit', 'route' => '/dashboard/bank-santri/top-up/cash', 'icon' => 'wallet', 'order' => 8],
            'Transfer Bank' => ['parent' => 'Bank Santri', 'en_title' => 'Bank Transfer', 'route' => '/dashboard/bank-santri/top-up/transfer', 'icon' => 'smartphone', 'order' => 9],

            // 5. LAPORAN KEUANGAN SUBMENUS
            'Jurnal Umum' => ['parent' => 'Laporan Keuangan', 'en_title' => 'General Journal', 'route' => '/dashboard/bank-santri/laporan/jurnal', 'icon' => 'file-text', 'order' => 1],
            'Mutasi Nasabah' => ['parent' => 'Laporan Keuangan', 'en_title' => 'Customer Mutation', 'route' => '/dashboard/bank-santri/laporan/mutasi', 'icon' => 'user-check', 'order' => 2],
            'Rekap Saldo' => ['parent' => 'Laporan Keuangan', 'en_title' => 'Balance Recap', 'route' => '/dashboard/bank-santri/laporan/saldo', 'icon' => 'landmark', 'order' => 3],
            'Rekap Kasir' => ['parent' => 'Laporan Keuangan', 'en_title' => 'Cashier Recap', 'route' => '/dashboard/bank-santri/laporan/kasir', 'icon' => 'receipt', 'order' => 4],
            'Konfigurasi Transaksi' => ['parent' => 'Laporan Keuangan', 'en_title' => 'Transaction Configuration', 'route' => '/dashboard/bank-santri/laporan/config', 'icon' => 'settings', 'order' => 5],

            // 6. MANAJEMEN BANK SUBMENUS
            'Produk Bank' => ['parent' => 'Manajemen Bank', 'en_title' => 'Bank Product', 'route' => '/dashboard/bank-santri/produk', 'icon' => 'box', 'order' => 1],
            'COA Bank' => ['parent' => 'Manajemen Bank', 'en_title' => 'Bank COA', 'route' => '/dashboard/bank-santri/coa', 'icon' => 'list', 'order' => 2],
            'Jenis Transaksi Bank' => ['parent' => 'Manajemen Bank', 'en_title' => 'Bank Transaction Type', 'route' => '/dashboard/bank-santri/jenis-transaksi', 'icon' => 'tags', 'order' => 3],
            'Pengaturan Bank' => ['parent' => 'Manajemen Bank', 'en_title' => 'Bank Setting', 'route' => '/dashboard/bank-santri/settings', 'icon' => 'settings', 'order' => 4],

            // 7. KAMTIB SUBMENUS
            'Pelanggaran' => ['parent' => 'Manajemen Kamtib', 'en_title' => 'Violation', 'route' => '/dashboard/manajemen-kamtib/pelanggaran', 'icon' => 'exclamation-triangle', 'order' => 1],
            'Kategori Pelanggaran' => ['parent' => 'Manajemen Kamtib', 'en_title' => 'Violation Category', 'route' => '/dashboard/manajemen-kamtib/kategori-pelanggaran', 'icon' => 'tags', 'order' => 2],
            'Sanksi' => ['parent' => 'Manajemen Kamtib', 'en_title' => 'Sanction', 'route' => '/dashboard/manajemen-kamtib/sanksi', 'icon' => 'gavel', 'order' => 3],
            'Perizinan' => ['parent' => 'Manajemen Kamtib', 'en_title' => 'Permit', 'route' => '/dashboard/manajemen-kamtib/perizinan', 'icon' => 'calendar-check', 'order' => 4],
            'Tipe Izin' => ['parent' => 'Manajemen Kamtib', 'en_title' => 'Permit Type', 'route' => '/dashboard/manajemen-kamtib/tipe-izin', 'icon' => 'clipboard', 'order' => 5],
            'Laporan Kamtib' => ['parent' => 'Manajemen Kamtib', 'en_title' => 'Security Report', 'route' => '/dashboard/manajemen-kamtib/laporan', 'icon' => 'files', 'order' => 6],
            'Manajemen Libur' => ['parent' => 'Manajemen Kamtib', 'en_title' => 'Holiday Management', 'route' => '/dashboard/manajemen-kamtib/manajemen-libur', 'icon' => 'calendar', 'order' => 7],
            'Verifikasi Libur' => ['parent' => 'Manajemen Kamtib', 'en_title' => 'Holiday Verification', 'route' => '/dashboard/manajemen-kamtib/libur-verifikasi', 'icon' => 'qr-code', 'order' => 10],

            // 8. KEPESANTRENAN SUBMENUS
            'Asrama' => ['parent' => 'Kepesantrenan', 'en_title' => 'Hostel', 'route' => '/dashboard/pendidikan/asrama', 'icon' => 'home', 'order' => 1],
            'Kamar' => ['parent' => 'Kepesantrenan', 'en_title' => 'Room', 'route' => '/dashboard/kepesantrenan/kamar', 'icon' => 'bed', 'order' => 2],

            // 9. MANAJEMEN PENDIDIKAN SUBMENUS
            'Program' => ['parent' => 'Manajemen Pendidikan', 'en_title' => 'Program Education', 'route' => '/dashboard/pendidikan/program', 'icon' => 'bookmark', 'order' => 1],
            'Tahun Ajaran' => ['parent' => 'Manajemen Pendidikan', 'en_title' => 'Academic Year', 'route' => '/dashboard/pendidikan/tahun-ajaran', 'icon' => 'calendar', 'order' => 2],
            'Jenjang' => ['parent' => 'Manajemen Pendidikan', 'en_title' => 'Level', 'route' => '/dashboard/pendidikan/jenjang', 'icon' => 'layers', 'order' => 3],
            'Kelas' => ['parent' => 'Manajemen Pendidikan', 'en_title' => 'Class Room', 'route' => '/dashboard/pendidikan/kelas', 'icon' => 'layout', 'order' => 4],
            'Rombel' => ['parent' => 'Manajemen Pendidikan', 'en_title' => 'Class Group', 'route' => '/dashboard/pendidikan/rombel', 'icon' => 'users', 'order' => 5],
            'Kelompok Pendidikan' => ['parent' => 'Manajemen Pendidikan', 'en_title' => 'Education Group', 'route' => '/dashboard/pendidikan/kelompok-pendidikan', 'icon' => 'graduation-cap', 'order' => 6],
            'Jadwal Kegiatan' => ['parent' => 'Manajemen Pendidikan', 'en_title' => 'Activity Schedule', 'route' => '/dashboard/jadwal', 'icon' => 'clock', 'order' => 7],

            // 10. INFORMASI SUBMENUS
            'Berita' => ['parent' => 'Informasi', 'en_title' => 'News Feed', 'route' => '/dashboard/berita', 'icon' => 'newspaper', 'order' => 1],
            'Pengumuman' => ['parent' => 'Informasi', 'en_title' => 'Announcement', 'route' => '/dashboard/pengumuman', 'icon' => 'bell', 'order' => 2],

            // 11. MANAJEMEN SANTRI SUBMENUS
            'Data Santri' => ['parent' => 'Manajemen Santri', 'en_title' => 'Student Data', 'route' => '/dashboard/santri', 'icon' => 'user-graduate', 'order' => 1],
            'Pendaftaran Santri' => ['parent' => 'Manajemen Santri', 'en_title' => 'Student Registration', 'route' => '/dashboard/pendaftaran-santri', 'icon' => 'user-plus', 'order' => 2],
            'Wali Santri' => ['parent' => 'Manajemen Santri', 'en_title' => 'Student Guardian', 'route' => '/dashboard/wali-santri-list', 'icon' => 'user-friends', 'order' => 3],
            'Mutasi Asrama' => ['parent' => 'Manajemen Santri', 'en_title' => 'Hostel Mutation', 'route' => '/dashboard/santri/mutasi-asrama', 'icon' => 'arrow-right-left', 'order' => 10],
            'Penempatan Kelas' => ['parent' => 'Manajemen Santri', 'en_title' => 'Class Placement', 'route' => '/dashboard/manajemen-kurikulum/penempatan-kelas', 'icon' => 'user-check', 'order' => 11],

            // 12. DATA MASTER SUBMENUS
            'Pekerjaan' => ['parent' => 'Data Master', 'en_title' => 'Occupation', 'route' => '/dashboard/master-data/pekerjaan', 'icon' => 'briefcase', 'order' => 1],
            'Provinsi' => ['parent' => 'Data Master', 'en_title' => 'Province', 'route' => '/dashboard/wilayah/provinsi', 'icon' => 'map', 'order' => 2],
            'Kota' => ['parent' => 'Data Master', 'en_title' => 'Regency', 'route' => '/dashboard/wilayah/kota', 'icon' => 'map-pinned', 'order' => 3],
            'Kecamatan' => ['parent' => 'Data Master', 'en_title' => 'District', 'route' => '/dashboard/wilayah/kecamatan', 'icon' => 'navigation', 'order' => 4],
            'Desa' => ['parent' => 'Data Master', 'en_title' => 'Village', 'route' => '/dashboard/wilayah/desa', 'icon' => 'map-pin', 'order' => 5],

            // 13. PENGATURAN SUBMENUS
            'Navigasi' => ['parent' => 'Pengaturan', 'en_title' => 'Navigation Management', 'route' => '/dashboard/settings/navigation', 'icon' => 'menu', 'order' => 1],
            'Profil Aplikasi' => ['parent' => 'Pengaturan', 'en_title' => 'App Profile', 'route' => '/dashboard/settings/app-profile', 'icon' => 'building', 'order' => 2],
            'Template Kartu Santri' => ['parent' => 'Pengaturan', 'en_title' => 'ID Card Template', 'route' => '/dashboard/settings/student-card-template', 'icon' => 'credit-card', 'order' => 3],
            'Peran & Izin' => ['parent' => 'Pengaturan', 'en_title' => 'Role & Permission', 'route' => '/dashboard/peran', 'icon' => 'shield', 'order' => 4],

            // 14. LAPORAN PESANTREN SUBMENUS
            'Statistik Santri' => ['parent' => 'Laporan Pesantren', 'en_title' => 'Student Statistics', 'route' => '/dashboard/kesantrian/laporan/statistik-santri', 'icon' => 'pie-chart', 'order' => 1],
            'Laporan Pelanggaran Pesantren' => ['parent' => 'Laporan Pesantren', 'en_title' => 'Violation Report Pesantren', 'route' => '/dashboard/kesantrian/laporan/pelanggaran', 'icon' => 'alert-triangle', 'order' => 2],
            'Laporan Izin Pesantren' => ['parent' => 'Laporan Pesantren', 'en_title' => 'Leave Report Pesantren', 'route' => '/dashboard/kesantrian/laporan/izin', 'icon' => 'calendar', 'order' => 3],
            'Statistik Presensi' => ['parent' => 'Laporan Pesantren', 'en_title' => 'Presence Statistics', 'route' => '/dashboard/kesantrian/laporan/presensi', 'icon' => 'check-square', 'order' => 4],
        ];

        // First pass: Create or update menus based on id_title
        // This avoids UniqueConstraintViolation on id_title
        foreach ($menusData as $id_title => $data) {
            Menu::updateOrCreate(
                ['id_title' => $id_title],
                [
                    'en_title' => $data['en_title'],
                    'icon' => $data['icon'],
                    'route' => $data['route'],
                    'parent_id' => null, // Temporarily set null for parents and submenus
                    'type' => isset($data['parent']) ? 'submenu' : 'main',
                    'position' => 'sidebar',
                    'status' => 'active',
                    'order' => $data['order'],
                ]
            );
        }

        // Second pass: Link submenus correctly to their parents
        foreach ($menusData as $id_title => $data) {
            if (isset($data['parent'])) {
                $parentMenu = Menu::where('id_title', $data['parent'])->first();
                if ($parentMenu) {
                    Menu::where('id_title', $id_title)->update(['parent_id' => $parentMenu->id]);
                }
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
