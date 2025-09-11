<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create permissions
        $p_dashboard = Permission::create(['name' => 'lihat dashboard']);
        $p_c_staff = Permission::create(['name' => 'buat staf']);
        $p_r_staff = Permission::create(['name' => 'lihat staf']);
        $p_u_staff = Permission::create(['name' => 'ubah staf']);
        $p_d_staff = Permission::create(['name' => 'hapus staf']);

        $p_c_santri = Permission::create(['name' => 'buat santri']);
        $p_r_santri = Permission::create(['name' => 'lihat santri']);
        $p_u_santri = Permission::create(['name' => 'ubah santri']);
        $p_d_santri = Permission::create(['name' => 'hapus santri']);
        $p_a_santri = Permission::create(['name' => 'aktivasi santri']);

        $p_c_parent = Permission::create(['name' => 'buat wali santri']);
        $p_r_parent = Permission::create(['name' => 'lihat wali santri']);
        $p_u_parent = Permission::create(['name' => 'ubah wali santri']);
        $p_d_parent = Permission::create(['name' => 'hapus wali santri']);

        $p_c_pendaftaran = Permission::create(['name' => 'buat pendaftaran']);
        $p_r_pendaftaran = Permission::create(['name' => 'lihat pendaftaran']);
        $p_u_pendaftaran = Permission::create(['name' => 'ubah pendaftaran']);
        $p_d_pendaftaran = Permission::create(['name' => 'hapus pendaftaran']);
        $p_a_pendaftaran = Permission::create(['name' => 'aktivasi pendaftaran']);

        $p_c_rekening = Permission::create(['name' => 'buat rekening']);
        $p_r_rekening = Permission::create(['name' => 'lihat rekening']);
        $p_u_rekening = Permission::create(['name' => 'ubah rekening']);
        $p_d_rekening = Permission::create(['name' => 'hapus rekening']);
        $p_a_rekening = Permission::create(['name' => 'aktivasi rekening']);

        $p_c_transaction = Permission::create(['name' => 'buat transaksi']);
        $p_r_transaction = Permission::create(['name' => 'lihat transaksi']);
        $p_u_transaction = Permission::create(['name' => 'ubah transaksi']);
        $p_d_transaction = Permission::create(['name' => 'hapus transaksi']);
        $p_a_transaction = Permission::create(['name' => 'aktivasi transaksi']);

        $p_c_matapelajaran = Permission::create(['name' => 'buat mata pelajaran']);
        $p_r_matapelajaran = Permission::create(['name' => 'lihat mata pelajaran']);
        $p_u_matapelajaran = Permission::create(['name' => 'ubah mata pelajaran']);
        $p_d_matapelajaran = Permission::create(['name' => 'hapus mata pelajaran']);

        $p_c_kelas = Permission::create(['name' => 'buat kelas']);
        $p_r_kelas = Permission::create(['name' => 'lihat kelas']);
        $p_u_kelas = Permission::create(['name' => 'ubah kelas']);
        $p_d_kelas = Permission::create(['name' => 'hapus kelas']);

        $p_c_rombel = Permission::create(['name' => 'buat rombel']);
        $p_r_rombel = Permission::create(['name' => 'lihat rombel']);
        $p_u_rombel = Permission::create(['name' => 'ubah rombel']);
        $p_d_rombel = Permission::create(['name' => 'hapus rombel']);

        $p_c_asrama = Permission::create(['name' => 'buat asrama']);
        $p_r_asrama = Permission::create(['name' => 'lihat asrama']);
        $p_u_asrama = Permission::create(['name' => 'ubah asrama']);
        $p_d_asrama = Permission::create(['name' => 'hapus asrama']);

        $p_c_program = Permission::create(['name' => 'buat program']);
        $p_r_program = Permission::create(['name' => 'lihat program']);
        $p_u_program = Permission::create(['name' => 'ubah program']);
        $p_d_program = Permission::create(['name' => 'hapus program']);

        $p_c_role = Permission::create(['name' => 'buat peran']);
        $p_r_role = Permission::create(['name' => 'lihat peran']);
        $p_u_role = Permission::create(['name' => 'ubah peran']);
        $p_d_role = Permission::create(['name' => 'hapus peran']);

        $p_c_permission = Permission::create(['name' => 'buat izin']);
        $p_r_permission = Permission::create(['name' => 'lihat izin']);
        $p_u_permission = Permission::create(['name' => 'ubah izin']);
        $p_d_permission = Permission::create(['name' => 'hapus izin']);

        $p_c_menu = Permission::create(['name' => 'buat menu']);
        $p_r_menu = Permission::create(['name' => 'lihat menu']);
        $p_u_menu = Permission::create(['name' => 'ubah menu']);
        $p_d_menu = Permission::create(['name' => 'hapus menu']);

        $p_c_setting = Permission::create(['name' => 'buat pengaturan']);
        $p_r_setting = Permission::create(['name' => 'lihat pengaturan']);
        $p_u_setting = Permission::create(['name' => 'ubah pengaturan']);
        $p_d_setting = Permission::create(['name' => 'hapus pengaturan']);

        $p_c_occupation = Permission::create(['name' => 'buat pekerjaan']);
        $p_r_occupation = Permission::create(['name' => 'lihat pekerjaan']);
        $p_u_occupation = Permission::create(['name' => 'ubah pekerjaan']);
        $p_d_occupation = Permission::create(['name' => 'hapus pekerjaan']);

        $p_c_camp = Permission::create(['name' => 'buat kamar']);
        $p_r_camp = Permission::create(['name' => 'lihat kamar']);
        $p_u_camp = Permission::create(['name' => 'ubah kamar']);
        $p_d_camp = Permission::create(['name' => 'hapus kamar']);

        $p_c_room = Permission::create(['name' => 'buat ruangan']);
        $p_r_room = Permission::create(['name' => 'lihat ruangan']);
        $p_u_room = Permission::create(['name' => 'ubah ruangan']);
        $p_d_room = Permission::create(['name' => 'hapus ruangan']);

        $p_c_year = Permission::create(['name' => 'buat tahun']);
        $p_r_year = Permission::create(['name' => 'lihat tahun']);
        $p_u_year = Permission::create(['name' => 'ubah tahun']);
        $p_d_year = Permission::create(['name' => 'hapus tahun']);

        $p_c_jobdesc = Permission::create(['name' => 'buat deskripsi pekerjaan']);
        $p_r_jobdesc = Permission::create(['name' => 'lihat deskripsi pekerjaan']);
        $p_u_jobdesc = Permission::create(['name' => 'ubah deskripsi pekerjaan']);
        $p_d_jobdesc = Permission::create(['name' => 'hapus deskripsi pekerjaan']);
        // role
        $r_superadmin = Role::where('name', 'superadmin')->first();
        $r_admin_bank = Role::create(['name' => 'admin bank']);
        $r_admin_layanan = Role::create(['name' => 'admin layanan']);
        $r_staf = Role::create(['name' => 'staf']);
        $r_guru_kelas = Role::create(['name' => 'guru kelas']);
        $r_asrama = Role::create(['name' => 'kepala asrama']);
        $r_walikelas = Role::create(['name' => 'wali kelas']);
        $r_orangtua = Role::where('name', 'orangtua')->first();
        $r_santri = Role::create(['name' => 'santri']);
        // give permission to role
        $r_superadmin->givePermissionTo(Permission::all());
        $r_admin_bank->givePermissionTo([$p_dashboard, $p_c_transaction, $p_r_transaction, $p_u_transaction, $p_d_transaction, $p_a_transaction]);
        // Define the menus to be seeded

        // menu menggunakan Licude icon
        $menus = [
            [
                'id_title' => 'Dasbor',
                'en_title' => 'Dashboard',
                'ar_title' => 'لوحة القيادة',
                'description' => 'Halaman utama dashboard',
                'icon' => 'layout-dashboard',
                'route' => '/dashboard',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            [
                'id_title' => 'Bank Santri',
                'en_title' => 'Bank Santri',
                'ar_title' => 'إدارة بنك الطلاب',
                'icon' => 'landmark',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            [
                'id_title' => 'Rekening',
                'en_title' => 'Bank Account',
                'ar_title' => 'حساب بنك',
                'icon' => 'landmark',
                'route' => '/dashboard/bank-santri/rekening',
                'parent_id' => 2,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            [
                'id_title' => 'Transaksi',
                'en_title' => 'Transaction',
                'ar_title' => 'معاملة',
                'icon' => 'money-bill-wave',
                'route' => '/dashboard/bank-santri/transaksi',
                'parent_id' => 2,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 4
            ],
            [
                'id_title' => 'Laporan',
                'en_title' => 'Report',
                'ar_title' => 'تقرير',
                'icon' => '',
                'route' => '/dashboard/bank-santri/laporan',
                'parent_id' => 2,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 5
            ],
            [
                'id_title' => 'Manajemen Santri',
                'en_title' => 'Santri Management',
                'ar_title' => 'إدارة Santri',
                'route' => '/dashboard/santri',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 6
            ],
            [
                'id_title' => 'Pendaftaran',
            ],
            [
                'id_title' => 'Wali Santri',
            ],
            [
                'id_title' => 'Manajemen Staf',
            ],
            [
                'id_title' => 'Staf',
            ],
            [
                'id_title' => 'Hak Akses',
            ],
            [
                'id_title' => 'Peran',
            ],
        ];

        // Insert each menu into the database
        foreach ($menus as $index => $menu) {
            $m_menu = Menu::create([
                'id_title' => $menu['id_title'],
                'en_title' => $menu['en_title'] ?? null,
                'ar_title' => $menu['ar_title'] ?? null,
                'description' => $menu['description'] ?? null,
                'icon' => $menu['icon'] ?? null,
                'route' => $menu['route'] ?? null,
                'parent_id' => $menu['parent_id'] ?? null,
                'type' => $menu['type'] ?? 'main',
                'position' => $menu['position'] ?? 'sidebar',
                'status' => $menu['status'] ?? 'active',
                'order' => $menu['order'] ?? $index + 1,
            ]);

            $m_menu->permissions()->attach([
                $p_c_room,
                $p_r_room,
                $p_u_room,
                $p_d_room,
                $p_c_year,
                $p_r_year,
                $p_u_year,
                $p_d_year,
                $p_c_jobdesc,
                $p_r_jobdesc,
                $p_u_jobdesc,
                $p_d_jobdesc,
                $p_c_transaction,
                $p_r_transaction,
                $p_u_transaction,
                $p_d_transaction,
                $p_a_transaction,
                $p_c_santri,
                $p_r_santri,
                $p_u_santri,
                $p_d_santri,
                $p_a_santri,
                $p_c_parent,
                $p_r_parent,
                $p_u_parent,
                $p_d_parent,
                $p_c_staff,
                $p_r_staff,
                $p_u_staff,
                $p_d_staff,
                $p_c_permission,
                $p_r_permission,
                $p_u_permission,
                $p_d_permission,
                $p_c_role,
                $p_r_role,
                $p_u_role,
                $p_d_role,
            ]);
        }
    }
}
