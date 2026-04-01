<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

$parentId = 2; // Bank Santri

$menuMappings = [
    '/dashboard/bank-santri' => [
        'id_title' => 'sidebar.bankDashboard',
        'en_title' => 'Bank Dashboard',
        'icon' => 'pie-chart',
        'order' => 1,
    ],
    '/dashboard/bank-santri/paket' => [
        'id_title' => 'sidebar.paymentPackage',
        'en_title' => 'Payment Packages',
        'icon' => 'package',
        'order' => 2,
    ],
    '/dashboard/bank-santri/pembayaran' => [
        'id_title' => 'sidebar.paymentProcess',
        'en_title' => 'Payment Processing',
        'icon' => 'credit-card',
        'order' => 3,
    ],
    '/dashboard/bank-santri/top-up/verifikasi' => [
        'id_title' => 'sidebar.topupVerification',
        'en_title' => 'Top-up Verification',
        'icon' => 'check-circle',
        'order' => 4,
    ],
    '/dashboard/bank-santri/rekening' => [
        'id_title' => 'sidebar.bankAccount',
        'en_title' => 'Bank Accounts',
        'icon' => 'users',
        'order' => 5,
    ],
    '/dashboard/bank-santri/koperasi' => [
        'id_title' => 'sidebar.koperasiPOS',
        'en_title' => 'Cooperative POS',
        'icon' => 'shopping-cart',
        'order' => 6,
    ],
    '/dashboard/bank-santri/pengaturan' => [
        'id_title' => 'sidebar.bankSettings',
        'en_title' => 'Bank Settings',
        'icon' => 'settings',
        'order' => 99,
    ],
];

try {
    DB::beginTransaction();

    foreach ($menuMappings as $route => $data) {
        $menu = Menu::where('route', $route)->first();
        if ($menu) {
            $menu->id_title = $data['id_title'];
            $menu->en_title = $data['en_title'];
            $menu->icon = $data['icon'];
            $menu->order = $data['order'];
            $menu->parent_id = $parentId; // Ensure correct parent
            $menu->save();
        } else {
            // Create if missing
            $menu = new Menu();
            $menu->id_title = $data['id_title'];
            $menu->en_title = $data['en_title'];
            $menu->route = $route;
            $menu->icon = $data['icon'];
            $menu->parent_id = $parentId;
            $menu->order = $data['order'];
            $menu->type = 'submenu';
            $menu->position = 'sidebar';
            $menu->status = 'active';
            $menu->save();
        }

        // Ensure roles are assigned (admin=2, superadmin=1)
        foreach ([1, 2] as $roleId) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $roleId, 'menu_id' => $menu->id],
                ['updated_at' => now()]
            );
        }
    }

    DB::commit();
    echo "Successfully refined Bank Santri menus with translation keys.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
