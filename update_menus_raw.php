<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // 1. Dapatkan ID Bank Santri
    $bankSantriId = DB::table('menus')->where('id_title', 'Bank Santri')->value('id');
    if (!$bankSantriId) {
        die("Bank Santri menu not found.\n");
    }

    // 2. Buat/Update Menu Laporan Keuangan
    $laporanId = DB::table('menus')->where('id_title', 'Laporan Keuangan')->where('parent_id', $bankSantriId)->value('id');
    if (!$laporanId) {
        $laporanId = DB::table('menus')->insertGetId([
            'id_title' => 'Laporan Keuangan',
            'en_title' => 'Financial Reports',
            'icon' => 'file-text',
            'type' => 'submenu',
            'position' => 'sidebar',
            'status' => 'active',
            'order' => 10,
            'parent_id' => $bankSantriId
        ]);
    }

    // 3. Buat Sub-menu
    $items = [
        ['id_title' => 'Jurnal Umum', 'en_title' => 'General Ledger', 'route' => '/dashboard/bank-santri/laporan/jurnal', 'icon' => 'book-text'],
        ['id_title' => 'Mutasi Nasabah', 'en_title' => 'Account Statement', 'route' => '/dashboard/bank-santri/laporan/mutasi', 'icon' => 'user-check'],
        ['id_title' => 'Rekap Saldo', 'en_title' => 'Balance Summary', 'route' => '/dashboard/bank-santri/laporan/saldo', 'icon' => 'landmark'],
        ['id_title' => 'Rekap Kasir', 'en_title' => 'Cashier Summary', 'route' => '/dashboard/bank-santri/laporan/kasir', 'icon' => 'receipt'],
        ['id_title' => 'Konfigurasi Transaksi', 'en_title' => 'Transaction Config', 'route' => '/dashboard/bank-santri/laporan/config', 'icon' => 'settings'],
    ];

    foreach ($items as $idx => $item) {
        $exists = DB::table('menus')->where('id_title', $item['id_title'])->where('parent_id', $laporanId)->exists();
        if (!$exists) {
            DB::table('menus')->insert(
                array_merge($item, [
                    'type' => 'submenu',
                    'position' => 'sidebar',
                    'status' => 'active',
                    'order' => $idx + 1,
                    'parent_id' => $laporanId
                ])
            );
        }
    }

    // 4. Hubungkan ke Peran (Raw SQL IGNORE)
    $allMenuIds = DB::table('menus')
        ->where('id', $laporanId)
        ->orWhere('parent_id', $laporanId)
        ->pluck('id');

    foreach ($allMenuIds as $mId) {
        foreach ([1, 2, 5] as $rId) {
            DB::statement("INSERT IGNORE INTO role_menu (role_id, menu_id) VALUES (?, ?)", [$rId, $mId]);
        }
    }

    echo "Menus successfully updated with correct enum 'submenu'.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
