<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

try {
    DB::transaction(function () {
        $bankSantri = Menu::where('id_title', 'Bank Santri')->first();
        if (!$bankSantri) {
            echo "Bank Santri menu not found.\n";
            return;
        }

        echo "Found Bank Santri ID: " . $bankSantri->id . "\n";

        // Create Sub-parent 'Laporan Keuangan'
        $laporanP = Menu::updateOrCreate(
            ['id_title' => 'Laporan Keuangan', 'parent_id' => $bankSantri->id],
            [
                'en_title' => 'Financial Reports',
                'icon' => 'file-text',
                'type' => 'sidebar',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 10
            ]
        );

        foreach ([1, 2, 5] as $roleId) {
            DB::statement("INSERT IGNORE INTO role_menu (menu_id, role_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$laporanP->id, $roleId]);
        }

        $items = [
            ['id_title' => 'Jurnal Umum', 'en_title' => 'General Ledger', 'route' => '/dashboard/bank-santri/laporan/jurnal', 'icon' => 'book-text'],
            ['id_title' => 'Mutasi Nasabah', 'en_title' => 'Account Statement', 'route' => '/dashboard/bank-santri/laporan/mutasi', 'icon' => 'user-check'],
            ['id_title' => 'Rekap Saldo', 'en_title' => 'Balance Summary', 'route' => '/dashboard/bank-santri/laporan/saldo', 'icon' => 'landmark'],
            ['id_title' => 'Rekap Kasir', 'en_title' => 'Cashier Summary', 'route' => '/dashboard/bank-santri/laporan/kasir', 'icon' => 'receipt'],
            ['id_title' => 'Konfigurasi Transaksi', 'en_title' => 'Transaction Config', 'route' => '/dashboard/bank-santri/laporan/config', 'icon' => 'settings'],
        ];

        foreach ($items as $idx => $item) {
            $m = Menu::updateOrCreate(
                ['id_title' => $item['id_title'], 'parent_id' => $laporanP->id],
                array_merge($item, [
                    'type' => 'sidebar',
                    'position' => 'sidebar',
                    'status' => 'active',
                    'order' => $idx + 1
                ])
            );
            foreach ([1, 2, 5] as $roleId) {
                DB::statement("INSERT IGNORE INTO role_menu (menu_id, role_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$m->id, $roleId]);
            }
        }

        echo "Menus successfully updated/created with parent ID " . $laporanP->id . "\n";
    });
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
