<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FrontendMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for all frontend routes if they don't exist
        // Dashboard permissions
        $p_dashboard = Permission::firstOrCreate(['name' => 'lihat dashboard']);

        // Role and Permission Management
        $p_c_role = Permission::firstOrCreate(['name' => 'buat peran']);
        $p_r_role = Permission::firstOrCreate(['name' => 'lihat peran']);
        $p_u_role = Permission::firstOrCreate(['name' => 'ubah peran']);
        $p_d_role = Permission::firstOrCreate(['name' => 'hapus peran']);

        $p_c_permission = Permission::firstOrCreate(['name' => 'buat izin']);
        $p_r_permission = Permission::firstOrCreate(['name' => 'lihat izin']);
        $p_u_permission = Permission::firstOrCreate(['name' => 'ubah izin']);
        $p_d_permission = Permission::firstOrCreate(['name' => 'hapus izin']);

        // Organization Management
        $p_c_organization = Permission::firstOrCreate(['name' => 'buat organisasi']);
        $p_r_organization = Permission::firstOrCreate(['name' => 'lihat organisasi']);
        $p_u_organization = Permission::firstOrCreate(['name' => 'ubah organisasi']);
        $p_d_organization = Permission::firstOrCreate(['name' => 'hapus organisasi']);

        // Staff Management
        $p_c_staff = Permission::firstOrCreate(['name' => 'buat staf']);
        $p_r_staff = Permission::firstOrCreate(['name' => 'lihat staf']);
        $p_u_staff = Permission::firstOrCreate(['name' => 'ubah staf']);
        $p_d_staff = Permission::firstOrCreate(['name' => 'hapus staf']);

        // Student Management
        $p_c_student = Permission::firstOrCreate(['name' => 'buat santri']);
        $p_r_student = Permission::firstOrCreate(['name' => 'lihat santri']);
        $p_u_student = Permission::firstOrCreate(['name' => 'ubah santri']);
        $p_d_student = Permission::firstOrCreate(['name' => 'hapus santri']);
        $p_a_student = Permission::firstOrCreate(['name' => 'aktivasi santri']);

        // Parent Management
        $p_c_parent = Permission::firstOrCreate(['name' => 'buat wali santri']);
        $p_r_parent = Permission::firstOrCreate(['name' => 'lihat wali santri']);
        $p_u_parent = Permission::firstOrCreate(['name' => 'ubah wali santri']);
        $p_d_parent = Permission::firstOrCreate(['name' => 'hapus wali santri']);

        // Student Registration
        $p_c_registration = Permission::firstOrCreate(['name' => 'buat pendaftaran']);
        $p_r_registration = Permission::firstOrCreate(['name' => 'lihat pendaftaran']);
        $p_u_registration = Permission::firstOrCreate(['name' => 'ubah pendaftaran']);
        $p_d_registration = Permission::firstOrCreate(['name' => 'hapus pendaftaran']);
        $p_a_registration = Permission::firstOrCreate(['name' => 'aktivasi pendaftaran']);

        // Banking & Finance
        $p_c_account = Permission::firstOrCreate(['name' => 'buat rekening']);
        $p_r_account = Permission::firstOrCreate(['name' => 'lihat rekening']);
        $p_u_account = Permission::firstOrCreate(['name' => 'ubah rekening']);
        $p_d_account = Permission::firstOrCreate(['name' => 'hapus rekening']);
        $p_a_account = Permission::firstOrCreate(['name' => 'aktivasi rekening']);

        $p_c_transaction = Permission::firstOrCreate(['name' => 'buat transaksi']);
        $p_r_transaction = Permission::firstOrCreate(['name' => 'lihat transaksi']);
        $p_u_transaction = Permission::firstOrCreate(['name' => 'ubah transaksi']);
        $p_d_transaction = Permission::firstOrCreate(['name' => 'hapus transaksi']);
        $p_a_transaction = Permission::firstOrCreate(['name' => 'aktivasi transaksi']);

        $p_c_product = Permission::firstOrCreate(['name' => 'buat produk']);
        $p_r_product = Permission::firstOrCreate(['name' => 'lihat produk']);
        $p_u_product = Permission::firstOrCreate(['name' => 'ubah produk']);
        $p_d_product = Permission::firstOrCreate(['name' => 'hapus produk']);

        $p_c_coa = Permission::firstOrCreate(['name' => 'buat coa']);
        $p_r_coa = Permission::firstOrCreate(['name' => 'lihat coa']);
        $p_u_coa = Permission::firstOrCreate(['name' => 'ubah coa']);
        $p_d_coa = Permission::firstOrCreate(['name' => 'hapus coa']);

        $p_c_transaction_type = Permission::firstOrCreate(['name' => 'buat jenis transaksi']);
        $p_r_transaction_type = Permission::firstOrCreate(['name' => 'lihat jenis transaksi']);
        $p_u_transaction_type = Permission::firstOrCreate(['name' => 'ubah jenis transaksi']);
        $p_d_transaction_type = Permission::firstOrCreate(['name' => 'hapus jenis transaksi']);

        $p_c_report = Permission::firstOrCreate(['name' => 'buat laporan']);
        $p_r_report = Permission::firstOrCreate(['name' => 'lihat laporan']);
        $p_u_report = Permission::firstOrCreate(['name' => 'ubah laporan']);
        $p_d_report = Permission::firstOrCreate(['name' => 'hapus laporan']);

        // Announcements & News
        $p_c_news = Permission::firstOrCreate(['name' => 'buat berita']);
        $p_r_news = Permission::firstOrCreate(['name' => 'lihat berita']);
        $p_u_news = Permission::firstOrCreate(['name' => 'ubah berita']);
        $p_d_news = Permission::firstOrCreate(['name' => 'hapus berita']);

        // Academic Management
        $p_c_assignment = Permission::firstOrCreate(['name' => 'buat tugas']);
        $p_r_assignment = Permission::firstOrCreate(['name' => 'lihat tugas']);
        $p_u_assignment = Permission::firstOrCreate(['name' => 'ubah tugas']);
        $p_d_assignment = Permission::firstOrCreate(['name' => 'hapus tugas']);

        $p_c_internship_supervisor = Permission::firstOrCreate(['name' => 'buat penanggung jawab magang']);
        $p_r_internship_supervisor = Permission::firstOrCreate(['name' => 'lihat penanggung jawab magang']);
        $p_u_internship_supervisor = Permission::firstOrCreate(['name' => 'ubah penanggung jawab magang']);
        $p_d_internship_supervisor = Permission::firstOrCreate(['name' => 'hapus penanggung jawab magang']);

        $p_c_educational_institution = Permission::firstOrCreate(['name' => 'buat institusi pendidikan']);
        $p_r_educational_institution = Permission::firstOrCreate(['name' => 'lihat institusi pendidikan']);
        $p_u_educational_institution = Permission::firstOrCreate(['name' => 'ubah institusi pendidikan']);
        $p_d_educational_institution = Permission::firstOrCreate(['name' => 'hapus institusi pendidikan']);

        // Geographic Management
        $p_c_province = Permission::firstOrCreate(['name' => 'buat provinsi']);
        $p_r_province = Permission::firstOrCreate(['name' => 'lihat provinsi']);
        $p_u_province = Permission::firstOrCreate(['name' => 'ubah provinsi']);
        $p_d_province = Permission::firstOrCreate(['name' => 'hapus provinsi']);

        $p_c_city = Permission::firstOrCreate(['name' => 'buat kota']);
        $p_r_city = Permission::firstOrCreate(['name' => 'lihat kota']);
        $p_u_city = Permission::firstOrCreate(['name' => 'ubah kota']);
        $p_d_city = Permission::firstOrCreate(['name' => 'hapus kota']);

        $p_c_district = Permission::firstOrCreate(['name' => 'buat kecamatan']);
        $p_r_district = Permission::firstOrCreate(['name' => 'lihat kecamatan']);
        $p_u_district = Permission::firstOrCreate(['name' => 'ubah kecamatan']);
        $p_d_district = Permission::firstOrCreate(['name' => 'hapus kecamatan']);

        $p_c_village = Permission::firstOrCreate(['name' => 'buat desa']);
        $p_r_village = Permission::firstOrCreate(['name' => 'lihat desa']);
        $p_u_village = Permission::firstOrCreate(['name' => 'ubah desa']);
        $p_d_village = Permission::firstOrCreate(['name' => 'hapus desa']);

        // Education Management
        $p_c_program = Permission::firstOrCreate(['name' => 'buat program']);
        $p_r_program = Permission::firstOrCreate(['name' => 'lihat program']);
        $p_u_program = Permission::firstOrCreate(['name' => 'ubah program']);
        $p_d_program = Permission::firstOrCreate(['name' => 'hapus program']);

        $p_c_academic_year = Permission::firstOrCreate(['name' => 'buat tahun ajaran']);
        $p_r_academic_year = Permission::firstOrCreate(['name' => 'lihat tahun ajaran']);
        $p_u_academic_year = Permission::firstOrCreate(['name' => 'ubah tahun ajaran']);
        $p_d_academic_year = Permission::firstOrCreate(['name' => 'hapus tahun ajaran']);

        $p_c_hostel = Permission::firstOrCreate(['name' => 'buat asrama']);
        $p_r_hostel = Permission::firstOrCreate(['name' => 'lihat asrama']);
        $p_u_hostel = Permission::firstOrCreate(['name' => 'ubah asrama']);
        $p_d_hostel = Permission::firstOrCreate(['name' => 'hapus asrama']);

        $p_c_education_type = Permission::firstOrCreate(['name' => 'buat jenjang pendidikan']);
        $p_r_education_type = Permission::firstOrCreate(['name' => 'lihat jenjang pendidikan']);
        $p_u_education_type = Permission::firstOrCreate(['name' => 'ubah jenjang pendidikan']);
        $p_d_education_type = Permission::firstOrCreate(['name' => 'hapus jenjang pendidikan']);

        $p_c_classroom = Permission::firstOrCreate(['name' => 'buat kelas']);
        $p_r_classroom = Permission::firstOrCreate(['name' => 'lihat kelas']);
        $p_u_classroom = Permission::firstOrCreate(['name' => 'ubah kelas']);
        $p_d_classroom = Permission::firstOrCreate(['name' => 'hapus kelas']);

        $p_c_class_group = Permission::firstOrCreate(['name' => 'buat rombel']);
        $p_r_class_group = Permission::firstOrCreate(['name' => 'lihat rombel']);
        $p_u_class_group = Permission::firstOrCreate(['name' => 'ubah rombel']);
        $p_d_class_group = Permission::firstOrCreate(['name' => 'hapus rombel']);

        $p_c_education = Permission::firstOrCreate(['name' => 'buat kelompok pendidikan']);
        $p_r_education = Permission::firstOrCreate(['name' => 'lihat kelompok pendidikan']);
        $p_u_education = Permission::firstOrCreate(['name' => 'ubah kelompok pendidikan']);
        $p_d_education = Permission::firstOrCreate(['name' => 'hapus kelompok pendidikan']);

        // Boarding Management
        $p_c_room = Permission::firstOrCreate(['name' => 'buat kamar']);
        $p_r_room = Permission::firstOrCreate(['name' => 'lihat kamar']);
        $p_u_room = Permission::firstOrCreate(['name' => 'ubah kamar']);
        $p_d_room = Permission::firstOrCreate(['name' => 'hapus kamar']);

        // Schedule Management
        $p_c_schedule = Permission::firstOrCreate(['name' => 'buat jadwal']);
        $p_r_schedule = Permission::firstOrCreate(['name' => 'lihat jadwal']);
        $p_u_schedule = Permission::firstOrCreate(['name' => 'ubah jadwal']);
        $p_d_schedule = Permission::firstOrCreate(['name' => 'hapus jadwal']);

        // Curriculum Management
        $p_c_subject = Permission::firstOrCreate(['name' => 'buat mata pelajaran']);
        $p_r_subject = Permission::firstOrCreate(['name' => 'lihat mata pelajaran']);
        $p_u_subject = Permission::firstOrCreate(['name' => 'ubah mata pelajaran']);
        $p_d_subject = Permission::firstOrCreate(['name' => 'hapus mata pelajaran']);

        $p_c_lesson_hour = Permission::firstOrCreate(['name' => 'buat jam pelajaran']);
        $p_r_lesson_hour = Permission::firstOrCreate(['name' => 'lihat jam pelajaran']);
        $p_u_lesson_hour = Permission::firstOrCreate(['name' => 'ubah jam pelajaran']);
        $p_d_lesson_hour = Permission::firstOrCreate(['name' => 'hapus jam pelajaran']);

        $p_c_student_curriculum = Permission::firstOrCreate(['name' => 'buat siswa kurikulum']);
        $p_r_student_curriculum = Permission::firstOrCreate(['name' => 'lihat siswa kurikulum']);
        $p_u_student_curriculum = Permission::firstOrCreate(['name' => 'ubah siswa kurikulum']);
        $p_d_student_curriculum = Permission::firstOrCreate(['name' => 'hapus siswa kurikulum']);

        $p_c_teacher = Permission::firstOrCreate(['name' => 'buat guru']);
        $p_r_teacher = Permission::firstOrCreate(['name' => 'lihat guru']);
        $p_u_teacher = Permission::firstOrCreate(['name' => 'ubah guru']);
        $p_d_teacher = Permission::firstOrCreate(['name' => 'hapus guru']);

        $p_c_teacher_assignment = Permission::firstOrCreate(['name' => 'buat penugasan guru']);
        $p_r_teacher_assignment = Permission::firstOrCreate(['name' => 'lihat penugasan guru']);
        $p_u_teacher_assignment = Permission::firstOrCreate(['name' => 'ubah penugasan guru']);
        $p_d_teacher_assignment = Permission::firstOrCreate(['name' => 'hapus penugasan guru']);

        $p_c_teaching_hours = Permission::firstOrCreate(['name' => 'buat jam mengajar']);
        $p_r_teaching_hours = Permission::firstOrCreate(['name' => 'lihat jam mengajar']);
        $p_u_teaching_hours = Permission::firstOrCreate(['name' => 'ubah jam mengajar']);
        $p_d_teaching_hours = Permission::firstOrCreate(['name' => 'hapus jam mengajar']);

        $p_c_presence = Permission::firstOrCreate(['name' => 'buat presensi']);
        $p_r_presence = Permission::firstOrCreate(['name' => 'lihat presensi']);
        $p_u_presence = Permission::firstOrCreate(['name' => 'ubah presensi']);
        $p_d_presence = Permission::firstOrCreate(['name' => 'hapus presensi']);

        $p_c_class_schedule = Permission::firstOrCreate(['name' => 'buat jadwal pelajaran']);
        $p_r_class_schedule = Permission::firstOrCreate(['name' => 'lihat jadwal pelajaran']);
        $p_u_class_schedule = Permission::firstOrCreate(['name' => 'ubah jadwal pelajaran']);
        $p_d_class_schedule = Permission::firstOrCreate(['name' => 'hapus jadwal pelajaran']);

        $p_c_promotion = Permission::firstOrCreate(['name' => 'buat kenaikan kelas']);
        $p_r_promotion = Permission::firstOrCreate(['name' => 'lihat kenaikan kelas']);
        $p_u_promotion = Permission::firstOrCreate(['name' => 'ubah kenaikan kelas']);
        $p_d_promotion = Permission::firstOrCreate(['name' => 'hapus kenaikan kelas']);

        // Security Management
        $p_c_violation = Permission::firstOrCreate(['name' => 'buat pelanggaran']);
        $p_r_violation = Permission::firstOrCreate(['name' => 'lihat pelanggaran']);
        $p_u_violation = Permission::firstOrCreate(['name' => 'ubah pelanggaran']);
        $p_d_violation = Permission::firstOrCreate(['name' => 'hapus pelanggaran']);

        $p_c_violation_category = Permission::firstOrCreate(['name' => 'buat kategori pelanggaran']);
        $p_r_violation_category = Permission::firstOrCreate(['name' => 'lihat kategori pelanggaran']);
        $p_u_violation_category = Permission::firstOrCreate(['name' => 'ubah kategori pelanggaran']);
        $p_d_violation_category = Permission::firstOrCreate(['name' => 'hapus kategori pelanggaran']);

        $p_c_sanction = Permission::firstOrCreate(['name' => 'buat sanksi']);
        $p_r_sanction = Permission::firstOrCreate(['name' => 'lihat sanksi']);
        $p_u_sanction = Permission::firstOrCreate(['name' => 'ubah sanksi']);
        $p_d_sanction = Permission::firstOrCreate(['name' => 'hapus sanksi']);

        $p_c_security_report = Permission::firstOrCreate(['name' => 'buat laporan kamtib']);
        $p_r_security_report = Permission::firstOrCreate(['name' => 'lihat laporan kamtib']);
        $p_u_security_report = Permission::firstOrCreate(['name' => 'ubah laporan kamtib']);
        $p_d_security_report = Permission::firstOrCreate(['name' => 'hapus laporan kamtib']);

        $p_c_leave = Permission::firstOrCreate(['name' => 'buat perizinan']);
        $p_r_leave = Permission::firstOrCreate(['name' => 'lihat perizinan']);
        $p_u_leave = Permission::firstOrCreate(['name' => 'ubah perizinan']);
        $p_d_leave = Permission::firstOrCreate(['name' => 'hapus perizinan']);

        $p_c_leave_type = Permission::firstOrCreate(['name' => 'buat tipe izin']);
        $p_r_leave_type = Permission::firstOrCreate(['name' => 'lihat tipe izin']);
        $p_u_leave_type = Permission::firstOrCreate(['name' => 'ubah tipe izin']);
        $p_d_leave_type = Permission::firstOrCreate(['name' => 'hapus tipe izin']);

        // Master Data
        $p_c_occupation = Permission::firstOrCreate(['name' => 'buat pekerjaan']);
        $p_r_occupation = Permission::firstOrCreate(['name' => 'lihat pekerjaan']);
        $p_u_occupation = Permission::firstOrCreate(['name' => 'ubah pekerjaan']);
        $p_d_occupation = Permission::firstOrCreate(['name' => 'hapus pekerjaan']);

        // Settings & Profile
        $p_c_navigation = Permission::firstOrCreate(['name' => 'buat navigasi']);
        $p_r_navigation = Permission::firstOrCreate(['name' => 'lihat navigasi']);
        $p_u_navigation = Permission::firstOrCreate(['name' => 'ubah navigasi']);
        $p_d_navigation = Permission::firstOrCreate(['name' => 'hapus navigasi']);

        $p_c_app_profile = Permission::firstOrCreate(['name' => 'buat profil aplikasi']);
        $p_r_app_profile = Permission::firstOrCreate(['name' => 'lihat profil aplikasi']);
        $p_u_app_profile = Permission::firstOrCreate(['name' => 'ubah profil aplikasi']);
        $p_d_app_profile = Permission::firstOrCreate(['name' => 'hapus profil aplikasi']);

        $p_c_user_profile = Permission::firstOrCreate(['name' => 'buat profil pengguna']);
        $p_r_user_profile = Permission::firstOrCreate(['name' => 'lihat profil pengguna']);
        $p_u_user_profile = Permission::firstOrCreate(['name' => 'ubah profil pengguna']);
        $p_d_user_profile = Permission::firstOrCreate(['name' => 'hapus profil pengguna']);

        // Get roles
        $r_superadmin = Role::where('name', 'superadmin')->first();

        // Define the menu structure based on frontend routes
        $menus = [
            // Dashboard Menu
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
            // Management Menu (Parent)
            [
                'id_title' => 'Manajemen',
                'en_title' => 'Management',
                'ar_title' => 'إدارة',
                'description' => 'Menu manajemen sistem',
                'icon' => 'settings',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // Staff Management
            [
                'id_title' => 'Manajemen Staf',
                'en_title' => 'Staff Management',
                'ar_title' => 'إدارة الموظفين',
                'description' => 'Manajemen data staf',
                'icon' => 'users',
                'route' => '/dashboard/staf',
                'parent_id' => 2, // Under Management
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            // Student Management
            [
                'id_title' => 'Manajemen Santri',
                'en_title' => 'Student Management',
                'ar_title' => 'إدارة الطلاب',
                'description' => 'Manajemen data santri',
                'icon' => 'graduation-cap',
                'route' => '/dashboard/santri',
                'parent_id' => 2, // Under Management
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // Parent Management
            [
                'id_title' => 'Manajemen Wali Santri',
                'en_title' => 'Parent Management',
                'ar_title' => 'إدارة أولياء الأمور',
                'description' => 'Manajemen data wali santri',
                'icon' => 'user-friends',
                'route' => '/dashboard/wali-santri-list',
                'parent_id' => 2, // Under Management
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            // Student Registration
            [
                'id_title' => 'Pendaftaran Santri',
                'en_title' => 'Student Registration',
                'ar_title' => 'تسجيل الطلاب',
                'description' => 'Manajemen pendaftaran santri',
                'icon' => 'user-plus',
                'route' => '/dashboard/pendaftaran-santri',
                'parent_id' => 2, // Under Management
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 4
            ],
            // Academic Menu (Parent)
            [
                'id_title' => 'Akademik',
                'en_title' => 'Academic',
                'ar_title' => 'أكاديمي',
                'description' => 'Menu manajemen akademik',
                'icon' => 'book',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            // Subject Management
            [
                'id_title' => 'Mata Pelajaran',
                'en_title' => 'Subject',
                'ar_title' => 'المادة الدراسية',
                'description' => 'Manajemen mata pelajaran',
                'icon' => 'book-open',
                'route' => '/dashboard/manajemen-kurikulum/mata-pelajaran',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            // Class Management
            [
                'id_title' => 'Kelas',
                'en_title' => 'Class',
                'ar_title' => 'فصل',
                'description' => 'Manajemen kelas',
                'icon' => 'chalkboard',
                'route' => '/dashboard/pendidikan/kelas',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // Class Group Management
            [
                'id_title' => 'Rombel',
                'en_title' => 'Class Group',
                'ar_title' => 'مجموعة الفصل',
                'description' => 'Manajemen rombel',
                'icon' => 'users',
                'route' => '/dashboard/pendidikan/rombel',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            // Teacher Management
            [
                'id_title' => 'Guru',
                'en_title' => 'Teacher',
                'ar_title' => 'معلم',
                'description' => 'Manajemen guru',
                'icon' => 'user-tie',
                'route' => '/dashboard/manajemen-kurikulum/guru',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 4
            ],
            // Teaching Assignment Management
            [
                'id_title' => 'Penugasan Guru',
                'en_title' => 'Teacher Assignment',
                'ar_title' => 'مهمة المعلم',
                'description' => 'Manajemen penugasan guru',
                'icon' => 'clipboard-list',
                'route' => '/dashboard/manajemen-kurikulum/penugasan-guru',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 5
            ],
            // Teaching Hours Management
            [
                'id_title' => 'Jam Mengajar',
                'en_title' => 'Teaching Hours',
                'ar_title' => 'ساعات التدريس',
                'description' => 'Manajemen jam mengajar',
                'icon' => 'clock',
                'route' => '/dashboard/manajemen-kurikulum/jam-mengajar',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 6
            ],
            // Class Schedule Management
            [
                'id_title' => 'Jadwal Pelajaran',
                'en_title' => 'Class Schedule',
                'ar_title' => 'جدول الفصل',
                'description' => 'Manajemen jadwal pelajaran',
                'icon' => 'calendar-alt',
                'route' => '/dashboard/manajemen-kurikulum/jadwal-pelajaran',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 7
            ],
            // Presence Management
            [
                'id_title' => 'Presensi',
                'en_title' => 'Attendance',
                'ar_title' => 'الحضور',
                'description' => 'Manajemen presensi',
                'icon' => 'check-circle',
                'route' => '/dashboard/manajemen-kurikulum/presensi',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 8
            ],
            // Promotion Management
            [
                'id_title' => 'Kenaikan Kelas',
                'en_title' => 'Class Promotion',
                'ar_title' => 'ترقية الفصل',
                'description' => 'Manajemen kenaikan kelas',
                'icon' => 'arrow-up',
                'route' => '/dashboard/manajemen-kurikulum/kenaikan-kelas',
                'parent_id' => 7, // Under Academic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 9
            ],
            // Security Menu (Parent)
            [
                'id_title' => 'Keamanan',
                'en_title' => 'Security',
                'ar_title' => 'الأمن',
                'description' => 'Menu manajemen keamanan',
                'icon' => 'shield-alt',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 4
            ],
            // Violation Management
            [
                'id_title' => 'Pelanggaran',
                'en_title' => 'Violation',
                'ar_title' => 'انتهاك',
                'description' => 'Manajemen pelanggaran',
                'icon' => 'exclamation-triangle',
                'route' => '/dashboard/manajemen-kamtib/pelanggaran',
                'parent_id' => 16, // Under Security
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            // Violation Category Management
            [
                'id_title' => 'Kategori Pelanggaran',
                'en_title' => 'Violation Category',
                'ar_title' => 'فئة الانتهاك',
                'description' => 'Manajemen kategori pelanggaran',
                'icon' => 'tags',
                'route' => '/dashboard/manajemen-kamtib/kategori-pelanggaran',
                'parent_id' => 16, // Under Security
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // Sanction Management
            [
                'id_title' => 'Sanksi',
                'en_title' => 'Sanction',
                'ar_title' => 'عقوبة',
                'description' => 'Manajemen sanksi',
                'icon' => 'ban',
                'route' => '/dashboard/manajemen-kamtib/sanksi',
                'parent_id' => 16, // Under Security
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            // Leave Management
            [
                'id_title' => 'Perizinan',
                'en_title' => 'Leave',
                'ar_title' => 'إجازة',
                'description' => 'Manajemen perizinan',
                'icon' => 'calendar-check',
                'route' => '/dashboard/manajemen-kamtib/perizinan',
                'parent_id' => 16, // Under Security
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 4
            ],
            // Leave Type Management
            [
                'id_title' => 'Tipe Izin',
                'en_title' => 'Leave Type',
                'ar_title' => 'نوع الإجازة',
                'description' => 'Manajemen tipe izin',
                'icon' => 'calendar-day',
                'route' => '/dashboard/manajemen-kamtib/tipe-izin',
                'parent_id' => 16, // Under Security
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 5
            ],
            // Security Report Management
            [
                'id_title' => 'Laporan Kamtib',
                'en_title' => 'Security Report',
                'ar_title' => 'تقرير الأمن',
                'description' => 'Manajemen laporan kamtib',
                'icon' => 'file-alt',
                'route' => '/dashboard/manajemen-kamtib/laporan',
                'parent_id' => 16, // Under Security
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 6
            ],
            // Finance Menu (Parent)
            [
                'id_title' => 'Keuangan',
                'en_title' => 'Finance',
                'ar_title' => 'المالية',
                'description' => 'Menu manajemen keuangan',
                'icon' => 'money-bill-wave',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 5
            ],
            // Banking Management
            [
                'id_title' => 'Bank Santri',
                'en_title' => 'Student Banking',
                'ar_title' => 'مصرف الطالب',
                'description' => 'Manajemen bank santri',
                'icon' => 'landmark',
                'route' => '/dashboard/bank-santri',
                'parent_id' => 23, // Under Finance
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            // Account Management
            [
                'id_title' => 'Rekening',
                'en_title' => 'Account',
                'ar_title' => 'حساب',
                'description' => 'Manajemen rekening',
                'icon' => 'wallet',
                'route' => '/dashboard/bank-santri/rekening',
                'parent_id' => 23, // Under Finance
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // Transaction Management
            [
                'id_title' => 'Transaksi',
                'en_title' => 'Transaction',
                'ar_title' => 'عملية',
                'description' => 'Manajemen transaksi',
                'icon' => 'exchange-alt',
                'route' => '/dashboard/bank-santri/transaksi',
                'parent_id' => 23, // Under Finance
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            // Product Management
            [
                'id_title' => 'Produk',
                'en_title' => 'Product',
                'ar_title' => 'منتج',
                'description' => 'Manajemen produk',
                'icon' => 'box',
                'route' => '/dashboard/bank-santri/produk',
                'parent_id' => 23, // Under Finance
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 4
            ],
            // COA Management
            [
                'id_title' => 'COA',
                'en_title' => 'Chart of Account',
                'ar_title' => 'مخطط الحساب',
                'description' => 'Manajemen chart of account',
                'icon' => 'list',
                'route' => '/dashboard/bank-santri/coa',
                'parent_id' => 23, // Under Finance
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 5
            ],
            // Transaction Type Management
            [
                'id_title' => 'Jenis Transaksi',
                'en_title' => 'Transaction Type',
                'ar_title' => 'نوع المعاملة',
                'description' => 'Manajemen jenis transaksi',
                'icon' => 'tags',
                'route' => '/dashboard/bank-santri/jenis-transaksi',
                'parent_id' => 23, // Under Finance
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 6
            ],
            // Finance Report Management
            [
                'id_title' => 'Laporan Keuangan',
                'en_title' => 'Finance Report',
                'ar_title' => 'تقرير مالي',
                'description' => 'Manajemen laporan keuangan',
                'icon' => 'file-invoice-dollar',
                'route' => '/dashboard/bank-santri/laporan',
                'parent_id' => 23, // Under Finance
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 7
            ],
            // System Menu (Parent)
            [
                'id_title' => 'Sistem',
                'en_title' => 'System',
                'ar_title' => 'نظام',
                'description' => 'Menu manajemen sistem',
                'icon' => 'cog',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 6
            ],
            // Role Management
            [
                'id_title' => 'Peran',
                'en_title' => 'Role',
                'ar_title' => 'دور',
                'description' => 'Manajemen peran pengguna',
                'icon' => 'user-shield',
                'route' => '/dashboard/peran',
                'parent_id' => 31, // Under System
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            // Permission Management
            [
                'id_title' => 'Hak Akses',
                'en_title' => 'Permission',
                'ar_title' => ' إذن',
                'description' => 'Manajemen hak akses',
                'icon' => 'key',
                'route' => '/dashboard/hak-akses',
                'parent_id' => 31, // Under System
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // Organization Management
            [
                'id_title' => 'Organisasi',
                'en_title' => 'Organization',
                'ar_title' => 'منظمة',
                'description' => 'Manajemen organisasi',
                'icon' => 'building',
                'route' => '/dashboard/organisasi',
                'parent_id' => 31, // Under System
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            // Master Data Menu (Parent)
            [
                'id_title' => 'Data Master',
                'en_title' => 'Master Data',
                'ar_title' => 'البيانات الرئيسية',
                'description' => 'Menu data master sistem',
                'icon' => 'database',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 7
            ],
            // Job Management
            [
                'id_title' => 'Pekerjaan',
                'en_title' => 'Job',
                'ar_title' => 'وظيفة',
                'description' => 'Manajemen data pekerjaan',
                'icon' => 'briefcase',
                'route' => '/dashboard/master-data/pekerjaan',
                'parent_id' => 35, // Under Master Data
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            // Education Type Management
            [
                'id_title' => 'Jenjang Pendidikan',
                'en_title' => 'Education Level',
                'ar_title' => 'مستوى التعليم',
                'description' => 'Manajemen jenjang pendidikan',
                'icon' => 'school',
                'route' => '/dashboard/pendidikan/jenjang',
                'parent_id' => 35, // Under Master Data
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // Program Management
            [
                'id_title' => 'Program',
                'en_title' => 'Program',
                'ar_title' => 'برنامج',
                'description' => 'Manajemen program pendidikan',
                'icon' => 'list-alt',
                'route' => '/dashboard/pendidikan/program',
                'parent_id' => 35, // Under Master Data
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            // Academic Year Management
            [
                'id_title' => 'Tahun Ajaran',
                'en_title' => 'Academic Year',
                'ar_title' => 'العام الدراسي',
                'description' => 'Manajemen tahun ajaran',
                'icon' => 'calendar',
                'route' => '/dashboard/pendidikan/tahun-ajaran',
                'parent_id' => 35, // Under Master Data
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 4
            ],
            // Hostel Management
            [
                'id_title' => 'Asrama',
                'en_title' => 'Hostel',
                'ar_title' => 'المبيت',
                'description' => 'Manajemen asrama',
                'icon' => 'home',
                'route' => '/dashboard/pendidikan/asrama',
                'parent_id' => 35, // Under Master Data
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 5
            ],
            // Room Management
            [
                'id_title' => 'Kamar',
                'en_title' => 'Room',
                'ar_title' => 'غرفة',
                'description' => 'Manajemen kamar asrama',
                'icon' => 'door-open',
                'route' => '/dashboard/kepesantrenan/kamar',
                'parent_id' => 35, // Under Master Data
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 6
            ],
            // Educational Institution Management
            [
                'id_title' => 'Institusi Pendidikan',
                'en_title' => 'Educational Institution',
                'ar_title' => 'مؤسسة تعليمية',
                'description' => 'Manajemen institusi pendidikan',
                'icon' => 'university',
                'route' => '/dashboard/manajemen-kurikulum/institusi-pendidikan',
                'parent_id' => 35, // Under Master Data
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 7
            ],
            // Geographic Management Menu (Parent)
            [
                'id_title' => 'Wilayah',
                'en_title' => 'Geographic',
                'ar_title' => 'جغرافي',
                'description' => 'Menu manajemen wilayah',
                'icon' => 'map-marked-alt',
                'route' => '#',
                'parent_id' => 35, // Under Master Data
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 8
            ],
            // Province Management
            [
                'id_title' => 'Provinsi',
                'en_title' => 'Province',
                'ar_title' => 'مقاطعة',
                'description' => 'Manajemen provinsi',
                'icon' => 'map',
                'route' => '/dashboard/wilayah/provinsi',
                'parent_id' => 43, // Under Geographic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            // City Management
            [
                'id_title' => 'Kota',
                'en_title' => 'City',
                'ar_title' => 'مدينة',
                'description' => 'Manajemen kota',
                'icon' => 'city',
                'route' => '/dashboard/wilayah/kota',
                'parent_id' => 43, // Under Geographic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // District Management
            [
                'id_title' => 'Kecamatan',
                'en_title' => 'District',
                'ar_title' => 'حي',
                'description' => 'Manajemen kecamatan',
                'icon' => 'road',
                'route' => '/dashboard/wilayah/kecamatan',
                'parent_id' => 43, // Under Geographic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 3
            ],
            // Village Management
            [
                'id_title' => 'Desa',
                'en_title' => 'Village',
                'ar_title' => 'قرية',
                'description' => 'Manajemen desa',
                'icon' => 'tree',
                'route' => '/dashboard/wilayah/desa',
                'parent_id' => 43, // Under Geographic
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 4
            ],
            // Announcement Management
            [
                'id_title' => 'Pengumuman',
                'en_title' => 'Announcement',
                'ar_title' => 'إعلان',
                'description' => 'Manajemen pengumuman',
                'icon' => 'bullhorn',
                'route' => '/dashboard/pengumuman',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 8
            ],
            // News Management
            [
                'id_title' => 'Berita',
                'en_title' => 'News',
                'ar_title' => 'أخبار',
                'description' => 'Manajemen berita',
                'icon' => 'newspaper',
                'route' => '/dashboard/berita',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 9
            ],
            // Schedule Management
            [
                'id_title' => 'Jadwal Kegiatan',
                'en_title' => 'Schedule',
                'ar_title' => 'جدول',
                'description' => 'Manajemen jadwal kegiatan',
                'icon' => 'calendar-alt',
                'route' => '/dashboard/jadwal',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 10
            ],
            // Settings Menu
            [
                'id_title' => 'Pengaturan',
                'en_title' => 'Settings',
                'ar_title' => 'إعدادات',
                'description' => 'Pengaturan sistem',
                'icon' => 'cog',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 11
            ],
            // Navigation Management
            [
                'id_title' => 'Manajemen Navigasi',
                'en_title' => 'Navigation Management',
                'ar_title' => 'إدارة التنقل',
                'description' => 'Manajemen navigasi sistem',
                'icon' => 'sitemap',
                'route' => '/dashboard/settings/navigation',
                'parent_id' => 50, // Under Settings
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 1
            ],
            // App Profile Management
            [
                'id_title' => 'Profil Aplikasi',
                'en_title' => 'App Profile',
                'ar_title' => 'ملف التطبيق',
                'description' => 'Manajemen profil aplikasi',
                'icon' => 'window-maximize',
                'route' => '/dashboard/settings/app-profile',
                'parent_id' => 50, // Under Settings
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 2
            ],
            // User Profile
            [
                'id_title' => 'Profil Pengguna',
                'en_title' => 'User Profile',
                'ar_title' => 'ملف المستخدم',
                'description' => 'Profil pengguna',
                'icon' => 'user',
                'route' => '/dashboard/profile',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 12
            ]
        ];

        // Insert each menu into the database and assign appropriate permissions
        foreach ($menus as $index => $menu) {
            $m_menu = Menu::firstOrCreate([
                'id_title' => $menu['id_title'],
            ], [
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

            // Define permissions for each menu based on its route
            $permissions_to_attach = [];

            // Dashboard permissions
            if (strpos($menu['route'], '/dashboard') !== false) {
                $permissions_to_attach[] = $p_dashboard;
            }

            // Management permissions
            if (strpos($menu['route'], '/dashboard/staf') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_staff, $p_r_staff, $p_u_staff, $p_d_staff
                ]);
            }

            if (strpos($menu['route'], '/dashboard/santri') !== false && strpos($menu['route'], 'pendaftaran') === false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_student, $p_r_student, $p_u_student, $p_d_student, $p_a_student
                ]);
            }

            if (strpos($menu['route'], '/dashboard/wali-santri') !== false && strpos($menu['route'], 'wali-santri-list') === false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_parent, $p_r_parent, $p_u_parent, $p_d_parent
                ]);
            }

            if (strpos($menu['route'], '/dashboard/pendaftaran-santri') !== false || strpos($menu['route'], 'calon-santri') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_registration, $p_r_registration, $p_u_registration, $p_d_registration, $p_a_registration
                ]);
            }

            // Banking & Finance permissions
            if (strpos($menu['route'], '/dashboard/bank-santri') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_account, $p_r_account, $p_u_account, $p_d_account, $p_a_account,
                    $p_c_transaction, $p_r_transaction, $p_u_transaction, $p_d_transaction, $p_a_transaction,
                    $p_c_product, $p_r_product, $p_u_product, $p_d_product,
                    $p_c_coa, $p_r_coa, $p_u_coa, $p_d_coa,
                    $p_c_transaction_type, $p_r_transaction_type, $p_u_transaction_type, $p_d_transaction_type,
                    $p_c_report, $p_r_report, $p_u_report, $p_d_report
                ]);
            }

            if (strpos($menu['route'], '/dashboard/bank-santri/rekening') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_account, $p_r_account, $p_u_account, $p_d_account, $p_a_account
                ]);
            }

            if (strpos($menu['route'], '/dashboard/bank-santri/transaksi') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_transaction, $p_r_transaction, $p_u_transaction, $p_d_transaction, $p_a_transaction
                ]);
            }

            if (strpos($menu['route'], '/dashboard/bank-santri/produk') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_product, $p_r_product, $p_u_product, $p_d_product
                ]);
            }

            if (strpos($menu['route'], '/dashboard/bank-santri/coa') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_coa, $p_r_coa, $p_u_coa, $p_d_coa
                ]);
            }

            if (strpos($menu['route'], '/dashboard/bank-santri/jenis-transaksi') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_transaction_type, $p_r_transaction_type, $p_u_transaction_type, $p_d_transaction_type
                ]);
            }

            if (strpos($menu['route'], '/dashboard/bank-santri/laporan') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_report, $p_r_report, $p_u_report, $p_d_report
                ]);
            }

            // Academic permissions
            if (strpos($menu['route'], '/dashboard/manajemen-kurikulum/mata-pelajaran') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_subject, $p_r_subject, $p_u_subject, $p_d_subject
                ]);
            }

            if (strpos($menu['route'], '/dashboard/pendidikan/kelas') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_classroom, $p_r_classroom, $p_u_classroom, $p_d_classroom
                ]);
            }

            if (strpos($menu['route'], '/dashboard/pendidikan/rombel') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_class_group, $p_r_class_group, $p_u_class_group, $p_d_class_group
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kurikulum/guru') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_teacher, $p_r_teacher, $p_u_teacher, $p_d_teacher
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kurikulum/penugasan-guru') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_teacher_assignment, $p_r_teacher_assignment, $p_u_teacher_assignment, $p_d_teacher_assignment
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kurikulum/jam-mengajar') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_teaching_hours, $p_r_teaching_hours, $p_u_teaching_hours, $p_d_teaching_hours
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kurikulum/jadwal-pelajaran') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_class_schedule, $p_r_class_schedule, $p_u_class_schedule, $p_d_class_schedule
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kurikulum/presensi') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_presence, $p_r_presence, $p_u_presence, $p_d_presence
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kurikulum/kenaikan-kelas') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_promotion, $p_r_promotion, $p_u_promotion, $p_d_promotion
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kurikulum/institusi-pendidikan') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_educational_institution, $p_r_educational_institution, $p_u_educational_institution, $p_d_educational_institution
                ]);
            }

            // Security permissions
            if (strpos($menu['route'], '/dashboard/manajemen-kamtib/pelanggaran') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_violation, $p_r_violation, $p_u_violation, $p_d_violation
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kamtib/kategori-pelanggaran') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_violation_category, $p_r_violation_category, $p_u_violation_category, $p_d_violation_category
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kamtib/sanksi') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_sanction, $p_r_sanction, $p_u_sanction, $p_d_sanction
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kamtib/laporan') !== false && strpos($menu['route'], 'kamtib') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_security_report, $p_r_security_report, $p_u_security_report, $p_d_security_report
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kamtib/perizinan') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_leave, $p_r_leave, $p_u_leave, $p_d_leave
                ]);
            }

            if (strpos($menu['route'], '/dashboard/manajemen-kamtib/tipe-izin') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_leave_type, $p_r_leave_type, $p_u_leave_type, $p_d_leave_type
                ]);
            }

            // System permissions
            if (strpos($menu['route'], '/dashboard/peran') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_role, $p_r_role, $p_u_role, $p_d_role
                ]);
            }

            if (strpos($menu['route'], '/dashboard/hak-akses') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_permission, $p_r_permission, $p_u_permission, $p_d_permission
                ]);
            }

            if (strpos($menu['route'], '/dashboard/organisasi') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_organization, $p_r_organization, $p_u_organization, $p_d_organization
                ]);
            }

            // Master data permissions
            if (strpos($menu['route'], '/dashboard/master-data/pekerjaan') !== false || strpos($menu['route'], '/dashboard/pekerjaan') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_occupation, $p_r_occupation, $p_u_occupation, $p_d_occupation
                ]);
            }

            if (strpos($menu['route'], '/dashboard/pendidikan/jenjang') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_education_type, $p_r_education_type, $p_u_education_type, $p_d_education_type
                ]);
            }

            if (strpos($menu['route'], '/dashboard/pendidikan/program') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_program, $p_r_program, $p_u_program, $p_d_program
                ]);
            }

            if (strpos($menu['route'], '/dashboard/pendidikan/tahun-ajaran') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_academic_year, $p_r_academic_year, $p_u_academic_year, $p_d_academic_year
                ]);
            }

            if (strpos($menu['route'], '/dashboard/pendidikan/asrama') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_hostel, $p_r_hostel, $p_u_hostel, $p_d_hostel
                ]);
            }

            if (strpos($menu['route'], '/dashboard/kepesantrenan/kamar') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_room, $p_r_room, $p_u_room, $p_d_room
                ]);
            }

            // Geographic permissions
            if (strpos($menu['route'], '/dashboard/wilayah/provinsi') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_province, $p_r_province, $p_u_province, $p_d_province
                ]);
            }

            if (strpos($menu['route'], '/dashboard/wilayah/kota') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_city, $p_r_city, $p_u_city, $p_d_city
                ]);
            }

            if (strpos($menu['route'], '/dashboard/wilayah/kecamatan') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_district, $p_r_district, $p_u_district, $p_d_district
                ]);
            }

            if (strpos($menu['route'], '/dashboard/wilayah/desa') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_village, $p_r_village, $p_u_village, $p_d_village
                ]);
            }

            // News permissions
            if (strpos($menu['route'], '/dashboard/berita') !== false || strpos($menu['route'], '/dashboard/pengumuman') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_news, $p_r_news, $p_u_news, $p_d_news
                ]);
            }

            // Schedule permissions
            if (strpos($menu['route'], '/dashboard/jadwal') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_schedule, $p_r_schedule, $p_u_schedule, $p_d_schedule
                ]);
            }

            // Settings permissions
            if (strpos($menu['route'], '/dashboard/settings/navigation') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_navigation, $p_r_navigation, $p_u_navigation, $p_d_navigation
                ]);
            }

            if (strpos($menu['route'], '/dashboard/settings/app-profile') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_app_profile, $p_r_app_profile, $p_u_app_profile, $p_d_app_profile
                ]);
            }

            if (strpos($menu['route'], '/dashboard/profile') !== false) {
                $permissions_to_attach = array_merge($permissions_to_attach, [
                    $p_c_user_profile, $p_r_user_profile, $p_u_user_profile, $p_d_user_profile
                ]);
            }

            // Sync permissions to the menu (this will replace existing permissions)
            if (!empty($permissions_to_attach)) {
                $m_menu->permissions()->sync($permissions_to_attach);
            }
        }

        // Give all permissions to superadmin role
        if ($r_superadmin) {
            $r_superadmin->givePermissionTo(Permission::all());
        }
    }
}
