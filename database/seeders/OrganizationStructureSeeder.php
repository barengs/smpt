<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Position;
use App\Models\Staff;
use App\Models\PositionAssignment;
use App\Models\User;

class OrganizationStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create organizations
        $school = Organization::create([
            'name' => 'SMP Negeri 1 Jakarta',
            'description' => 'Sekolah Menengah Pertama Negeri 1 Jakarta',
            'code' => 'SMPN1JKT',
            'parent_id' => null,
            'level' => 1,
            'is_active' => true,
        ]);

        $administration = Organization::create([
            'name' => 'Administrasi Sekolah',
            'description' => 'Bagian administrasi sekolah',
            'code' => 'ADMIN',
            'parent_id' => $school->id,
            'level' => 2,
            'is_active' => true,
        ]);

        $academic = Organization::create([
            'name' => 'Bagian Akademik',
            'description' => 'Bagian akademik sekolah',
            'code' => 'ACAD',
            'parent_id' => $school->id,
            'level' => 2,
            'is_active' => true,
        ]);

        // Create positions
        $principal = Position::create([
            'name' => 'Kepala Sekolah',
            'code' => 'KS',
            'description' => 'Kepala Sekolah SMP Negeri 1 Jakarta',
            'organization_id' => $school->id,
            'parent_id' => null,
            'level' => 1,
            'is_active' => true,
        ]);

        $adminHead = Position::create([
            'name' => 'Kepala Administrasi',
            'code' => 'KA',
            'description' => 'Kepala Bagian Administrasi',
            'organization_id' => $administration->id,
            'parent_id' => $principal->id,
            'level' => 2,
            'is_active' => true,
        ]);

        $academicHead = Position::create([
            'name' => 'Kepala Bagian Akademik',
            'code' => 'KAA',
            'description' => 'Kepala Bagian Akademik',
            'organization_id' => $academic->id,
            'parent_id' => $principal->id,
            'level' => 2,
            'is_active' => true,
        ]);

        $financeStaff = Position::create([
            'name' => 'Staf Keuangan',
            'code' => 'SK',
            'description' => 'Staf Bagian Keuangan',
            'organization_id' => $administration->id,
            'parent_id' => $adminHead->id,
            'level' => 3,
            'is_active' => true,
        ]);

        $teacher = Position::create([
            'name' => 'Guru',
            'code' => 'GR',
            'description' => 'Guru Mata Pelajaran',
            'organization_id' => $academic->id,
            'parent_id' => $academicHead->id,
            'level' => 3,
            'is_active' => true,
        ]);

        // Create users for staff
        $user1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@school.id',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'Ani Wijaya',
            'email' => 'ani.wijaya@school.id',
            'password' => bcrypt('password'),
        ]);

        $user3 = User::create([
            'name' => 'Joko Susilo',
            'email' => 'joko.susilo@school.id',
            'password' => bcrypt('password'),
        ]);

        $user4 = User::create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti.rahmawati@school.id',
            'password' => bcrypt('password'),
        ]);

        $user5 = User::create([
            'name' => 'Ahmad Prasetyo',
            'email' => 'ahmad.prasetyo@school.id',
            'password' => bcrypt('password'),
        ]);

        // Create staff
        $staff1 = Staff::create([
            'user_id' => $user1->id,
            'code' => 'STF001',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'nip' => '198001012005121001',
            'nik' => '3171010101800001',
            'email' => 'budi.santoso@school.id',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 1, Jakarta',
            'photo' => null,
            'marital_status' => 'Menikah',
            'status' => 'Aktif',
        ]);

        $staff2 = Staff::create([
            'user_id' => $user2->id,
            'code' => 'STF002',
            'first_name' => 'Ani',
            'last_name' => 'Wijaya',
            'nip' => '198502022008122002',
            'nik' => '3171010101850002',
            'email' => 'ani.wijaya@school.id',
            'phone' => '081234567891',
            'address' => 'Jl. Sudirman No. 2, Jakarta',
            'photo' => null,
            'marital_status' => 'Menikah',
            'status' => 'Aktif',
        ]);

        $staff3 = Staff::create([
            'user_id' => $user3->id,
            'code' => 'STF003',
            'first_name' => 'Joko',
            'last_name' => 'Susilo',
            'nip' => '198203032007121003',
            'nik' => '3171010101820003',
            'email' => 'joko.susilo@school.id',
            'phone' => '081234567892',
            'address' => 'Jl. Thamrin No. 3, Jakarta',
            'photo' => null,
            'marital_status' => 'Menikah',
            'status' => 'Aktif',
        ]);

        $staff4 = Staff::create([
            'user_id' => $user4->id,
            'code' => 'STF004',
            'first_name' => 'Siti',
            'last_name' => 'Rahmawati',
            'nip' => '198704042010122004',
            'nik' => '3171010101870004',
            'email' => 'siti.rahmawati@school.id',
            'phone' => '081234567893',
            'address' => 'Jl. Gatot Subroto No. 4, Jakarta',
            'photo' => null,
            'marital_status' => 'Menikah',
            'status' => 'Aktif',
        ]);

        $staff5 = Staff::create([
            'user_id' => $user5->id,
            'code' => 'STF005',
            'first_name' => 'Ahmad',
            'last_name' => 'Prasetyo',
            'nip' => '198305052009121005',
            'nik' => '3171010101830005',
            'email' => 'ahmad.prasetyo@school.id',
            'phone' => '081234567894',
            'address' => 'Jl. Diponegoro No. 5, Jakarta',
            'photo' => null,
            'marital_status' => 'Menikah',
            'status' => 'Aktif',
        ]);

        // Create position assignments
        PositionAssignment::create([
            'position_id' => $principal->id,
            'staff_id' => $staff1->id,
            'start_date' => '2020-01-01',
            'end_date' => null,
            'assignment_letter' => 'SK/001/2020',
            'notes' => 'Ditetapkan sebagai Kepala Sekolah',
            'is_active' => true,
        ]);

        PositionAssignment::create([
            'position_id' => $adminHead->id,
            'staff_id' => $staff2->id,
            'start_date' => '2020-01-01',
            'end_date' => null,
            'assignment_letter' => 'SK/002/2020',
            'notes' => 'Ditetapkan sebagai Kepala Administrasi',
            'is_active' => true,
        ]);

        PositionAssignment::create([
            'position_id' => $academicHead->id,
            'staff_id' => $staff3->id,
            'start_date' => '2020-01-01',
            'end_date' => null,
            'assignment_letter' => 'SK/003/2020',
            'notes' => 'Ditetapkan sebagai Kepala Bagian Akademik',
            'is_active' => true,
        ]);

        PositionAssignment::create([
            'position_id' => $financeStaff->id,
            'staff_id' => $staff4->id,
            'start_date' => '2020-01-01',
            'end_date' => null,
            'assignment_letter' => 'SK/004/2020',
            'notes' => 'Ditetapkan sebagai Staf Keuangan',
            'is_active' => true,
        ]);

        PositionAssignment::create([
            'position_id' => $teacher->id,
            'staff_id' => $staff5->id,
            'start_date' => '2020-01-01',
            'end_date' => null,
            'assignment_letter' => 'SK/005/2020',
            'notes' => 'Ditetapkan sebagai Guru',
            'is_active' => true,
        ]);
    }
}
