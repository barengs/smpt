<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HolidayVerificationMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find the Parent "Manajemen Kamtib"
        $parent = Menu::where('id_title', 'Manajemen Kamtib')->first();
        
        if (!$parent) {
            // Fallback: Create it if not exists (though it should exist)
            $parent = Menu::create([
                'id_title' => 'Manajemen Kamtib',
                'en_title' => 'Security & Discipline',
                'ar_title' => 'الأمن والضبط',
                'description' => 'Manajemen keamanan dan ketertiban santri',
                'icon' => 'shield-check',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 40,
            ]);
        }

        // 2. Create the "Verifikasi Libur" Submenu
        $submenu = Menu::updateOrCreate(
            ['route' => '/dashboard/manajemen-kamtib/libur-verifikasi'],
            [
                'id_title' => 'Verifikasi Libur',
                'en_title' => 'Holiday Verification',
                'ar_title' => 'التحقق من الإجازة',
                'description' => 'Verifikasi check-in/out santri libur menggunakan NIS/QR',
                'icon' => 'qr-code',
                'parent_id' => $parent->id,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 25, // Adjust order as needed
            ]
        );

        // 3. Assign Permission
        $permName = 'lihat verifikasi libur';
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
