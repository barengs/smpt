<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class RoleAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'superadmin' => [
                'category' => 'administrasi',
                'menus' => Menu::all()->pluck('id_title')->toArray(), // All menus
                'permissions' => ['view', 'create', 'edit', 'delete', 'approve'] // Full access
            ],
            'admin' => [
                'category' => 'administrasi',
                'menus' => [
                    'Dasbor', 'Manajemen Santri', 'Data Santri', 'Pendaftaran Santri', 'Wali Santri',
                    'Manajemen Staf', 'Data Staf', 'Manajemen Pendidikan', 'Institusi Pendidikan',
                    'Informasi', 'Pengaturan'
                ],
                'permissions' => ['view', 'create', 'edit', 'delete', 'approve']
            ],
            'staf' => [
                'category' => 'administrasi',
                'menus' => [
                    'Dasbor', 'Manajemen Santri', 'Data Santri', 'Pendaftaran Santri', 'Wali Santri'
                ],
                'permissions' => ['view', 'create', 'edit'] // No delete/approve
            ],
            'guru' => [
                'category' => 'pendidikan',
                'menus' => [
                    'Dasbor', 'Kurikulum', 'Mata Pelajaran', 'Jadwal Pelajaran', 'Guru', 
                    'Penugasan Guru', 'Jam Mengajar', 'Presensi', 'Kenaikan Kelas', 'Siswa'
                ],
                'permissions' => ['view'] // Mostly view, presensi maybe edit?
            ],
            'kepala sekolah' => [
                'category' => 'pendidikan',
                'menus' => Menu::all()->pluck('id_title')->toArray(),
                'permissions' => ['view', 'approve']
            ]
        ];

        foreach ($roles as $roleName => $config) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            
            // Update category if configured and column exists
            if (isset($config['category'])) {
                $role->category = $config['category'];
                $role->save();
            }

            $menuIds = [];
            $allPermissionIds = [];

            // Find IDs for menu names
            foreach ($config['menus'] as $menuTitle) {
                // Try finding by id_title or en_title
                $menu = Menu::where('id_title', $menuTitle)
                    ->orWhere('en_title', $menuTitle)
                    ->first();
                
                if ($menu) {
                    $menuIds[] = $menu->id;

                    // Generate Permissions
                    $menuSlug = Str::slug($menu->en_title ?? $menu->id_title);
                    
                    foreach ($config['permissions'] as $action) {
                        $idnAction = match($action) {
                            'view' => 'lihat',
                            'create' => 'buat',
                            'edit' => 'ubah',
                            'update' => 'ubah',
                            'delete' => 'hapus',
                            'approve' => 'aktivasi',
                            default => $action
                        };

                        $permName = "{$idnAction} {$menuSlug}";
                        $permission = Permission::firstOrCreate(['name' => $permName]);
                        $allPermissionIds[] = $permission->id;
                    }
                }
            }

            // Sync Menus
            $role->menus()->sync($menuIds);

            // Sync Permissions
            $role->syncPermissions($allPermissionIds);

            $this->command->info("Role {$roleName} synced with " . count($menuIds) . " menus and permissions.");
        }
    }
}
