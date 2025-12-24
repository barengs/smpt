<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all roles and menus
        $roles = Role::all();
        $menus = Menu::all();

        // For each role, assign appropriate menus based on role permissions
        foreach ($roles as $role) {
            // Get all permissions for this role
            $rolePermissions = $role->permissions;

            // Find menus that have at least one of the role's permissions
            $menuIds = [];
            foreach ($menus as $menu) {
                $menuPermissions = $menu->permissions;

                // Check if there's any intersection between role permissions and menu permissions
                foreach ($menuPermissions as $menuPermission) {
                    if ($rolePermissions->contains('id', $menuPermission->id)) {
                        $menuIds[] = $menu->id;
                        break; // No need to check other permissions for this menu
                    }
                }
            }

            // Assign menus to role
            if (!empty($menuIds)) {
                $role->menus()->sync($menuIds);
            }
        }

        // For superadmin role, assign all menus
        $superadmin = Role::where('name', 'superadmin')->first();
        if ($superadmin) {
            $superadmin->menus()->sync($menus->pluck('id')->toArray());
        }
    }
}
