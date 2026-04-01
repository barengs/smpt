<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temukan MENU Kurikulum
        $kurikulum = Menu::where('id_title', 'Kurikulum')->first();
        
        if ($kurikulum) {
            // Tambah Menu Penempatan Kelas
            $penempatanKelas = Menu::updateOrCreate(
                ['route' => '/dashboard/manajemen-kurikulum/penempatan-kelas'],
                [
                    'id_title' => 'Penempatan Kelas',
                    'en_title' => 'Class Placement',
                    'ar_title' => 'توزيع الفصول',
                    'description' => 'Penempatan santri baru ke dalam kelas',
                    'icon' => 'user-plus',
                    'parent_id' => $kurikulum->id,
                    'type' => 'submenu',
                    'position' => 'sidebar',
                    'status' => 'active',
                    'order' => 68, // Sebelum Kenaikan Kelas (biasanya 69/70)
                ]
            );

            // Berikan akses ke superadmin
            $superadmin = Role::where('name', 'superadmin')->first();
            if ($superadmin) {
                DB::table('role_menu')->updateOrInsert(
                    ['role_id' => $superadmin->id, 'menu_id' => $penempatanKelas->id]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Menu::where('route', '/dashboard/manajemen-kurikulum/penempatan-kelas')->delete();
    }
};
