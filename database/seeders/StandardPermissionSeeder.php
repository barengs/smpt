<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class StandardPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates 5 standard permissions that can be used across all menus:
     * - CREATE: Permission to create new records
     * - VIEW: Permission to view/read records
     * - EDIT: Permission to update existing records
     * - DELETE: Permission to delete records
     * - APPROVE: Permission to approve/activate records
     */
    public function run(): void
    {
        $standardPermissions = [
            [
                'name' => 'CREATE',
                'guard_name' => 'api',
            ],
            [
                'name' => 'VIEW',
                'guard_name' => 'api',
            ],
            [
                'name' => 'EDIT',
                'guard_name' => 'api',
            ],
            [
                'name' => 'DELETE',
                'guard_name' => 'api',
            ],
            [
                'name' => 'APPROVE',
                'guard_name' => 'api',
            ],
        ];

        foreach ($standardPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']]
            );
            
            $this->command->info("Permission '{$permission['name']}' created or already exists.");
        }

        $this->command->info('Standard permissions seeded successfully!');
    }
}
