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
        $parent = DB::table('menus')->where('id_title', 'Manajemen Kamtib')->first();
        if ($parent) {
            $menuId = DB::table('menus')->insertGetId([
                'id_title' => 'Jenis Sanksi',
                'route' => '/dashboard/manajemen-kamtib/jenis-sanksi',
                'parent_id' => $parent->id,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Give access to admin (role_id 1)
            DB::table('role_menu')->insert([
                'role_id' => 1,
                'menu_id' => $menuId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menus')->where('route', '/dashboard/manajemen-kamtib/jenis-sanksi')->delete();
    }
};
