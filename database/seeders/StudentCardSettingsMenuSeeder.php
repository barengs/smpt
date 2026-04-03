<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentCardSettingsMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find the Parent "Pengaturan" (Settings)
        $parent = Menu::where('id_title', 'Pengaturan')->first();
        
        if (!$parent) {
            // Fallback: Create it if not exists (though it should exist)
            $parent = Menu::create([
                'id_title' => 'Pengaturan',
                'en_title' => 'Settings',
                'ar_title' => 'إعدادات',
                'description' => 'Menu pengaturan sistem',
                'icon' => 'settings',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 100,
            ]);
        }

        // 2. Create the "Template Kartu Santri" Submenu
        $submenu = Menu::updateOrCreate(
            ['route' => '/dashboard/settings/student-card-template'],
            [
                'id_title' => 'Template Kartu Santri',
                'en_title' => 'Student Card Template',
                'ar_title' => 'قالب بطاقة الطالب',
                'description' => 'Pengaturan desain dan template kartu santri',
                'icon' => 'card-text', // Or something similar
                'parent_id' => $parent->id,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 10,
            ]
        );

        // 3. Assign Permission
        $permName = 'lihat pengaturan kartu';
        $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'api']);
        
        DB::table('menu_permissions')->updateOrInsert(
            ['menu_id' => $submenu->id, 'permission_id' => $permission->id]
        );

        // 4. Assign to Superadmin & Administrasi Kepesantrenan roles
        $roles = Role::whereIn('name', ['superadmin', 'administrasi', 'Admin Kepesantrenan'])->get();
        foreach ($roles as $role) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $role->id, 'menu_id' => $submenu->id]
            );
            
            // Also assign the direct permission if using Spatie
            $role->givePermissionTo($permission);
        }
    }
}
