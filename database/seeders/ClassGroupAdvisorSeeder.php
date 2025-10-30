<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassGroup;
use App\Models\Staff;
use Spatie\Permission\Models\Role;

class ClassGroupAdvisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the 'walikelas' role
        $walikelasRole = Role::where('name', 'walikelas')->first();

        if (!$walikelasRole) {
            echo "Role 'walikelas' not found. Please run RoleSeeder first.\n";
            return;
        }

        // Get all staff members
        $staffMembers = Staff::all();

        // Assign the 'walikelas' role to some staff members if they don't already have it
        foreach ($staffMembers as $index => $staff) {
            // Assign the role to about 30% of staff members
            if ($index % 3 == 0 && $staff->user) {
                $staff->user->assignRole($walikelasRole);
            }
        }

        // Get all class groups
        $classGroups = ClassGroup::all();

        // Get staff members with 'walikelas' role
        $advisorStaff = Staff::whereHas('user', function ($query) {
            $query->role('walikelas');
        })->get();

        // Assign advisors to class groups
        foreach ($classGroups as $index => $classGroup) {
            if ($advisorStaff->count() > 0) {
                // Assign an advisor to each class group
                $advisor = $advisorStaff->get($index % $advisorStaff->count());
                $classGroup->update(['advisor_id' => $advisor->id]);
            }
        }
    }
}
