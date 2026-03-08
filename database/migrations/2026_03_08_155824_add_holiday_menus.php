<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $kamtibId = 8; // ID for Manajemen Kamtib parent menu

        // Insert Manajemen Libur
        $managementId = DB::table('menus')->insertGetId([
            'parent_id' => $kamtibId,
            'id_title' => 'Manajemen Libur',
            'route' => '/dashboard/manajemen-kamtib/manajemen-libur',
            'icon' => 'Calendar',
            'order' => 5,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert Libur Santri
        $studentId = DB::table('menus')->insertGetId([
            'parent_id' => $kamtibId,
            'id_title' => 'Libur Santri',
            'route' => '/dashboard/manajemen-kamtib/libur-santri',
            'icon' => 'Printer',
            'order' => 6,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Give access to superadmin (1) and admin (2)
        foreach ([1, 2] as $roleId) {
            DB::table('role_menu')->insert([
                ['role_id' => $roleId, 'menu_id' => $managementId, 'created_at' => now(), 'updated_at' => now()],
                ['role_id' => $roleId, 'menu_id' => $studentId, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down()
    {
        $menuIds = DB::table('menus')
            ->whereIn('id_title', ['Manajemen Libur', 'Libur Santri'])
            ->pluck('id');

        DB::table('role_menu')->whereIn('menu_id', $menuIds)->delete();
        DB::table('menus')->whereIn('id', $menuIds)->delete();
    }
};
