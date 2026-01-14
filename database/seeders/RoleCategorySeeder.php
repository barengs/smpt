<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define your categories and roles here
        $categories = [
            'pendidikan' => ['guru', 'walikelas', 'kepala_sekolah', 'asatidz'],
            'administrasi' => ['admin', 'keuangan', 'kepala_asrama', 'keamanan', 'staf'],
        ];

        foreach ($categories as $category => $roles) {
            foreach ($roles as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $role->update(['category' => $category]);
                    $this->command->info("Role {$roleName} updated with category {$category}");
                } else {
                    $this->command->warn("Role {$roleName} not found");
                }
            }
        }
    }
}
