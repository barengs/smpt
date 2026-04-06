<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find parent menu "Manajemen Santri"
        $parent = DB::table('menus')->where('id_title', 'Manajemen Santri')->first();
        
        if (!$parent) {
            return;
        }

        // Check if menu already exists
        $exists = DB::table('menus')->where('id_title', 'Manajemen Perjanjian')->exists();
        if ($exists) {
            return;
        }

        // Insert new menu
        $menuId = DB::table('menus')->insertGetId([
            'id_title' => 'Manajemen Perjanjian',
            'en_title' => 'Agreement Management',
            'icon' => 'ClipboardList',
            'route' => '/dashboard/santri/perjanjian',
            'parent_id' => $parent->id,
            'type' => 'submenu',
            'order' => 10, // Adjust order as needed
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign to Superadmin (1) and Admin (2) roles
        foreach ([1, 2] as $roleId) {
            DB::table('role_menu')->insert([
                'role_id' => $roleId,
                'menu_id' => $menuId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menu = DB::table('menus')->where('id_title', 'Manajemen Perjanjian')->first();
        if ($menu) {
            DB::table('role_menu')->where('menu_id', $menu->id)->delete();
            DB::table('menus')->where('id', $menu->id)->delete();
        }
    }
};
