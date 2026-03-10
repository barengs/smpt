<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * PermissionMatrixSeeder
 *
 * Mengisi matrix permission dengan format scoped: {action}_menu_{id}
 * yang dibutuhkan oleh endpoint GET /roles/{id}/permission-matrix.
 *
 * AMAN dijalankan tanpa mengganggu data yang sudah ada:
 * - Menggunakan firstOrCreate untuk permissions (tidak duplikasi)
 * - Menggunakan syncWithoutDetaching untuk menu-permission pivot (tidak hapus data lain)
 * - Menambah permission ke role, tidak menghapus yang sudah ada
 */
class PermissionMatrixSeeder extends Seeder
{
    /**
     * Daftar aksi standar yang tersedia.
     * Format scoped: {action}_menu_{menuId}
     * Contoh: view_menu_1, create_menu_2, edit_menu_3
     */
    private array $standardActions = ['view', 'create', 'edit', 'delete', 'approve'];

    /**
     * Konfigurasi hak akses per peran.
     * Key: nama peran, Value: array aksi yang diperbolehkan
     */
    private array $roleConfig = [
        'superadmin' => ['view', 'create', 'edit', 'delete', 'approve'],
        'admin'      => ['view', 'create', 'edit', 'delete', 'approve'],
        'staf'       => ['view', 'create', 'edit'],
        'guru'       => ['view'],
        'kepala sekolah' => ['view', 'approve'],
    ];

    public function run(): void
    {
        $menus = Menu::all();
        $roles = Role::all();

        $this->command->info('Memulai seeding Permission Matrix...');
        $this->command->info("Total menu: {$menus->count()}, Total role: {$roles->count()}");

        DB::beginTransaction();

        try {
            foreach ($roles as $role) {
                // Tentukan aksi yang diperbolehkan untuk peran ini
                $allowedActions = $this->getActionsForRole($role->name);

                if (empty($allowedActions)) {
                    $this->command->warn("  Role '{$role->name}' tidak punya konfigurasi, skip.");
                    continue;
                }

                $this->command->info("Processing role: {$role->name} (actions: " . implode(', ', $allowedActions) . ")");

                // Untuk superadmin, gunakan semua menu dari database
                // Untuk role lain, gunakan menu yang sudah ter-assign ke role tersebut
                $roleMenus = ($role->name === 'superadmin')
                    ? $menus
                    : $role->menus;

                $newPermissionNames = [];

                foreach ($roleMenus as $menu) {
                    $menuPermissionIds = [];

                    foreach ($allowedActions as $action) {
                        $scopedName = "{$action}_menu_{$menu->id}";

                        // Buat permission jika belum ada (aman, tidak duplikasi)
                        $permission = Permission::firstOrCreate([
                            'name'       => $scopedName,
                            'guard_name' => 'api',
                        ]);

                        $menuPermissionIds[] = $permission->id;
                        $newPermissionNames[] = $scopedName;
                    }

                    // Hubungkan permission ke menu (tanpa menghapus yang sudah ada)
                    if ($menu->permissions()->exists() || count($menuPermissionIds) > 0) {
                        $menu->permissions()->syncWithoutDetaching($menuPermissionIds);
                    }
                }

                // Tambahkan permissions baru ke role tanpa menghapus yang lama
                if (!empty($newPermissionNames)) {
                    // Dapatkan ID permissions yang sudah ada di role
                    $existingPermIds = $role->permissions()->pluck('id')->toArray();
                    // Dapatkan ID permissions baru
                    $newPermIds = Permission::whereIn('name', $newPermissionNames)
                        ->pluck('id')
                        ->toArray();
                    // Gabungkan dan sync (tidak ada pengurangan)
                    $mergedIds = array_unique(array_merge($existingPermIds, $newPermIds));
                    $role->syncPermissions($mergedIds);

                    $this->command->info("  -> Ditambahkan " . count($newPermIds) . " scoped permissions ke role '{$role->name}'");
                }
            }

            DB::commit();
            $this->command->info('');
            $this->command->info('Permission Matrix berhasil di-seed!');
            $this->command->info('Sekarang endpoint /roles/{id}/permission-matrix akan mengembalikan data yang benar.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mendapatkan daftar aksi yang diperbolehkan berdasarkan nama peran.
     * Jika nama peran tidak ada dalam konfigurasi, fallback ke 'view' saja.
     */
    private function getActionsForRole(string $roleName): array
    {
        $lowerName = strtolower($roleName);

        foreach ($this->roleConfig as $configKey => $actions) {
            if ($lowerName === strtolower($configKey)) {
                return $actions;
            }
        }

        // Fallback: semua role yang tidak terdaftar minimal dapat 'view'
        return ['view'];
    }
}
