<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'superadmin',
            'admin',
            'asatidz',
            'walikelas',
            'teller',
            'orangtua',
            'adminbank',
            'staf',
        ];

        foreach ($roles as $roleName) {
            Role::updateOrCreate(
                ['name' => $roleName, 'guard_name' => 'api'],
                ['name' => $roleName, 'guard_name' => 'api']
            );
        }
    }
}
