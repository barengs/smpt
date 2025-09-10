<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'guard_name' => 'api',
            ],
            [
                'name' => 'admin',
                'guard_name' => 'api',
            ],
            [
                'name' => 'asatidz',
                'guard_name' => 'api',
            ],
            [
                'name' => 'walikelas',
                'guard_name' => 'api',
            ],
            [
                'name' => 'kasir',
                'guard_name' => 'api',
            ],
            [
                'name' => 'orangtua',
                'guard_name' => 'api',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
