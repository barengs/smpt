<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            [
                'name' => 'superadmin',
                'email' => 'superadmin@mail.com',
                'password' => $password,
                'role' => 'superadmin',
            ],
            [
                'name' => 'adminbank',
                'email' => 'adminbank@mail.com',
                'password' => $password,
                'role' => 'adminbank',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Ensure role exists before assigning
            if (Role::where('name', $roleName)->exists()) {
                $user->syncRoles([$roleName]);
            }
        }
    }
}
