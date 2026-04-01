<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

$parentId = 2; // Bank Santri
$rolesToAssign = [1, 2]; // Superadmin, Admin

$newMenus = [
    [
        'id_title' => 'Dashboard Bank',
        'en_title' => 'Bank Dashboard',
        'route' => '/dashboard/bank-santri',
        'icon' => 'pie-chart',
        'order' => 1,
    ],
    [
        'id_title' => 'Paket Pembayaran',
        'en_title' => 'Payment Packages',
        'route' => '/dashboard/bank-santri/paket',
        'icon' => 'package',
        'order' => 2,
    ],
    [
        'id_title' => 'Proses Pembayaran',
        'en_title' => 'Payment Processing',
        'route' => '/dashboard/bank-santri/pembayaran',
        'icon' => 'credit-card',
        'order' => 3,
    ],
    [
        'id_title' => 'Kasir Koperasi',
        'en_title' => 'Cooperative POS',
        'route' => '/dashboard/bank-santri/koperasi',
        'icon' => 'shopping-cart',
        'order' => 6,
    ],
    [
        'id_title' => 'Pengaturan Bank',
        'en_title' => 'Bank Settings',
        'route' => '/dashboard/bank-santri/pengaturan',
        'icon' => 'settings',
        'order' => 99,
    ],
];

try {
    DB::beginTransaction();

    foreach ($newMenus as $menuData) {
        $menu = Menu::where('route', $menuData['route'])->first();
        if (!$menu) {
            $menu = new Menu();
        }
        
        $menu->id_title = $menuData['id_title'];
        $menu->en_title = $menuData['en_title'];
        $menu->route = $menuData['route'];
        $menu->icon = $menuData['icon'];
        $menu->parent_id = $parentId;
        $menu->order = $menuData['order'];
        $menu->type = 'submenu';
        $menu->position = 'sidebar';
        $menu->status = 'active';
        $menu->save();

        // Assign to roles
        foreach ($rolesToAssign as $roleId) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $roleId, 'menu_id' => $menu->id],
                ['updated_at' => now()]
            );
        }
    }

    // Assign parent menu to roles
    foreach ($rolesToAssign as $roleId) {
        DB::table('role_menu')->updateOrInsert(
            ['role_id' => $roleId, 'menu_id' => $parentId],
            ['updated_at' => now()]
        );
    }

    DB::commit();
    echo "Successfully updated Bank Santri menus.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
