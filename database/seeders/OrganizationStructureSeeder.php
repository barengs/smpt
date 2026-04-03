<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Position;
use App\Models\Staff;
use App\Models\PositionAssignment;

class OrganizationStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create organizations
        $school = Organization::updateOrCreate(
            ['code' => 'SMPN1JKT'],
            [
                'name' => 'SMP Panyeppen',
                'description' => 'Institusi Pendidikan SMP Panyeppen',
                'parent_id' => null,
                'level' => 1,
                'is_active' => true,
            ]
        );

        $administration = Organization::updateOrCreate(
            ['code' => 'ADMIN'],
            [
                'name' => 'Administrasi Sekolah',
                'description' => 'Bagian administrasi sekolah',
                'parent_id' => $school->id,
                'level' => 2,
                'is_active' => true,
            ]
        );

        $academic = Organization::updateOrCreate(
            ['code' => 'ACAD'],
            [
                'name' => 'Bagian Akademik',
                'description' => 'Bagian akademik sekolah',
                'parent_id' => $school->id,
                'level' => 2,
                'is_active' => true,
            ]
        );

        // 2. Create positions
        $principal = Position::updateOrCreate(
            ['code' => 'KS'],
            [
                'name' => 'Kepala Sekolah',
                'description' => 'Kepala Sekolah SMP Panyeppen',
                'organization_id' => $school->id,
                'parent_id' => null,
                'level' => 1,
                'is_active' => true,
            ]
        );

        $adminHead = Position::updateOrCreate(
            ['code' => 'KA'],
            [
                'name' => 'Kepala Administrasi',
                'description' => 'Kepala Bagian Administrasi',
                'organization_id' => $administration->id,
                'parent_id' => $principal->id,
                'level' => 2,
                'is_active' => true,
            ]
        );

        $academicHead = Position::updateOrCreate(
            ['code' => 'KAA'],
            [
                'name' => 'Kepala Bagian Akademik',
                'description' => 'Kepala Bagian Akademik',
                'organization_id' => $academic->id,
                'parent_id' => $principal->id,
                'level' => 2,
                'is_active' => true,
            ]
        );

        // 3. Assign Existing Super Admin Staff to key positions
        // This satisfies the structure without creating extra users
        $staff1 = Staff::where('code', 'SA-001')->first();

        if ($staff1) {
            PositionAssignment::updateOrCreate(
                ['position_id' => $principal->id, 'staff_id' => $staff1->id],
                [
                    'start_date' => '2020-01-01',
                    'end_date' => null,
                    'assignment_letter' => 'SK/001/2020',
                    'notes' => 'Ditetapkan sebagai Kepala Sekolah',
                    'is_active' => true,
                ]
            );
        }
    }
}
