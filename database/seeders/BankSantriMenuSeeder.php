<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class BankSantriMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the Menu
        $menu = Menu::updateOrCreate(
            ['id_title' => 'menu_bank_santri'],
            [
                'en_title'    => 'Bank Santri',
                'ar_title'    => 'بنك سانتري',
                'description' => 'Akses Dashboard Perbankan Santri',
                'icon'        => 'Banknote', // Lucide icon name
                'route'       => 'auth/sso-handover',
                'parent_id'   => null,
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 100, // Put it at the end
            ]
        );

        // 2. Link to Roles (superadmin: 1, admin: 2, teller: 5, adminbank: 7)
        $roleIds = [1, 2, 5, 7];
        
        foreach ($roleIds as $roleId) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $roleId, 'menu_id' => $menu->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
