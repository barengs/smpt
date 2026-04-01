<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TopUpMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Top-Up permissions
        $p_c_topup = Permission::firstOrCreate(['name' => 'buat topup']);
        $p_r_topup = Permission::firstOrCreate(['name' => 'lihat topup']);
        $p_v_topup = Permission::firstOrCreate(['name' => 'verifikasi topup']);

        // 2. Assign to roles
        $r_superadmin = Role::where('name', 'superadmin')->first();
        $r_admin_bank = Role::where('name', 'admin bank')->first();
        $r_orangtua = Role::where('name', 'orangtua')->first();
        
        // Superadmin gets all
        if ($r_superadmin) {
            $r_superadmin->givePermissionTo([$p_c_topup, $p_r_topup, $p_v_topup]);
        }
        
        // Admin Bank gets verification and creation capabilities
        if ($r_admin_bank) {
            $r_admin_bank->givePermissionTo([$p_c_topup, $p_r_topup, $p_v_topup]);
        }
        
        // Parent gets viewing and creation (uploading transfer proof)
        if ($r_orangtua) {
            $r_orangtua->givePermissionTo([$p_c_topup, $p_r_topup]);
        }

        // 3. Find the 'Bank Santri' parent menu
        $parentMenu = Menu::where('id_title', 'Bank Santri')
                          ->orWhere('en_title', 'Bank Santri')
                          ->first();

        $parentId = $parentMenu ? $parentMenu->id : null;

        // 4. Create menus
        $menus = [
            [
                'id_title' => 'Top-Up / Setor Tunai',
                'en_title' => 'Cash Top-Up',
                'ar_title' => 'الإيداع النقدي',
                'icon' => 'wallet',
                'route' => '/dashboard/bank-santri/top-up/cash',
                'parent_id' => $parentId,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 10
            ],
            [
                'id_title' => 'Transfer Bank',
                'en_title' => 'Bank Transfer',
                'ar_title' => 'تحويل بنكي',
                'icon' => 'smartphone-nfc',
                'route' => '/dashboard/bank-santri/top-up/transfer',
                'parent_id' => $parentId,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 11
            ],
            [
                'id_title' => 'Verifikasi Top-Up',
                'en_title' => 'Top-Up Verifications',
                'ar_title' => 'التحقق من الشحن',
                'icon' => 'check-circle-2',
                'route' => '/dashboard/bank-santri/top-up/verifikasi',
                'parent_id' => $parentId,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 12
            ]
        ];

        foreach ($menus as $menu) {
            $m = Menu::updateOrCreate(
                ['route' => $menu['route']], // Unique constraint for updating safely
                $menu
            );

            // Assign permissions to menu
            if ($menu['route'] == '/dashboard/bank-santri/top-up/verifikasi') {
                $m->permissions()->syncWithoutDetaching([$p_v_topup->id]);
            } else {
                $m->permissions()->syncWithoutDetaching([$p_r_topup->id, $p_c_topup->id]);
            }
        }
    }
}
