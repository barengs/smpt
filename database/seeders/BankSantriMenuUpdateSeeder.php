<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSantriMenuUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Parent "Bank Santri" exists
        $bankSantri = Menu::updateOrCreate(
            ['id_title' => 'Bank Santri'],
            [
                'en_title' => 'Student Bank', // Unique English Title
                'ar_title' => 'البنك الطلابي',
                'description' => 'Menu manajemen bank santri',
                'icon' => 'landmark',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 20,
            ]
        );

        // 2. Ensure Parent "Laporan Keuangan" exists
        $laporanKeuangan = Menu::updateOrCreate(
            ['id_title' => 'Laporan Keuangan'],
            [
                'en_title' => 'Finance Report Center', // Unique English Title
                'ar_title' => 'مركز التقارير المالية',
                'description' => 'Menu laporan keuangan bank santri',
                'icon' => 'receipt',
                'route' => '#',
                'parent_id' => null,
                'type' => 'main',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => 21,
            ]
        );

        // Submenus for Bank Santri
        $bankSantriSubmenus = [
            ['id_title' => 'Transaksi', 'en_title' => 'Bank Transactions', 'route' => '/dashboard/bank-santri/transaksi', 'icon' => 'refresh-cw', 'order' => 1],
            ['id_title' => 'Paket Pembayaran', 'en_title' => 'Payment Pkg', 'route' => '/dashboard/bank-santri/payment-package', 'icon' => 'package', 'order' => 2],
            ['id_title' => 'Proses Pembayaran', 'en_title' => 'Exec Payment', 'route' => '/dashboard/bank-santri/payment-process', 'icon' => 'credit-card', 'order' => 3],
            ['id_title' => 'Verifikasi Top-up', 'en_title' => 'Verify Topup', 'route' => '/dashboard/bank-santri/top-up/verifikasi', 'icon' => 'check-circle', 'order' => 4],
            ['id_title' => 'Rekening Bank', 'en_title' => 'Bank Accs', 'route' => '/dashboard/bank-santri/rekening', 'icon' => 'users', 'order' => 5],
            ['id_title' => 'Kasir Koperasi', 'en_title' => 'Coop Cashier', 'route' => '/dashboard/bank-santri/koperasi', 'icon' => 'shopping-cart', 'order' => 6],
            ['id_title' => 'Dashboard Bank', 'en_title' => 'Bank Dash', 'route' => '/dashboard/bank-santri/dashboard', 'icon' => 'pie-chart', 'order' => 7],
            ['id_title' => 'Top-Up / Setor Tunai', 'en_title' => 'Cash Topup', 'route' => '/dashboard/bank-santri/top-up/cash', 'icon' => 'wallet', 'order' => 8],
            ['id_title' => 'Transfer Bank', 'en_title' => 'Acc Transfer', 'route' => '/dashboard/bank-santri/top-up/transfer', 'icon' => 'smartphone', 'order' => 9],
            ['id_title' => 'Pengaturan Bank', 'en_title' => 'Bank Config', 'route' => '/dashboard/bank-santri/settings', 'icon' => 'settings', 'order' => 10],
        ];

        // Submenus for Laporan Keuangan
        $laporanKeuanganSubmenus = [
            ['id_title' => 'Jurnal Umum', 'en_title' => 'General Lgr', 'route' => '/dashboard/keuangan/laporan/jurnal-umum', 'icon' => 'file-text', 'order' => 1],
            ['id_title' => 'Mutasi Nasabah', 'en_title' => 'Customer Stmt', 'route' => '/dashboard/keuangan/laporan/mutasi-nasabah', 'icon' => 'user-check', 'order' => 2],
            ['id_title' => 'Rekap Saldo', 'en_title' => 'Bal Summary', 'route' => '/dashboard/keuangan/laporan/rekap-saldo', 'icon' => 'landmark', 'order' => 3],
            ['id_title' => 'Rekap Kasir', 'en_title' => 'Cash Recap', 'route' => '/dashboard/keuangan/laporan/rekap-kasir', 'icon' => 'receipt', 'order' => 4],
            ['id_title' => 'Konfigurasi Transaksi', 'en_title' => 'Tx Config', 'route' => '/dashboard/keuangan/pengaturan-transaksi', 'icon' => 'settings', 'order' => 5],
        ];

        $superadmin = Role::where('name', 'superadmin')->first();

        // Helper function to update or create menu with conflict handling
        $upsertMenu = function($parent_id, $submenu) use ($superadmin) {
            // Find by route or id_title
            $menu = Menu::where('route', $submenu['route'])
                        ->orWhere('id_title', $submenu['id_title'])
                        ->first();

            $data = [
                'id_title' => $submenu['id_title'],
                'en_title' => $submenu['en_title'],
                'icon' => $submenu['icon'],
                'parent_id' => $parent_id,
                'type' => 'submenu',
                'position' => 'sidebar',
                'status' => 'active',
                'order' => $submenu['order'],
                'route' => $submenu['route'],
            ];

            if ($menu) {
                // Check for EN title conflict if changing
                if (isset($submenu['en_title']) && $menu->en_title !== $submenu['en_title']) {
                    $conflict = Menu::where('en_title', $submenu['en_title'])->where('id', '!=', $menu->id)->exists();
                    if ($conflict) {
                        unset($data['en_title']); // Don't update if it causes conflict
                    }
                }
                $menu->update($data);
            } else {
                // Check if en_title exists
                if (isset($submenu['en_title'])) {
                    $conflict = Menu::where('en_title', $submenu['en_title'])->exists();
                    if ($conflict) {
                        $data['en_title'] = $submenu['en_title'] . ' ' . $submenu['order']; // Append order to make unique
                    }
                }
                $menu = Menu::create($data);
            }

            // Permission check
            $permName = 'lihat ' . strtolower($submenu['id_title']);
            $permission = Permission::firstOrCreate(['name' => $permName]);
            
            DB::table('menu_permissions')->updateOrInsert(
                ['menu_id' => $menu->id, 'permission_id' => $permission->id]
            );

            if ($superadmin) {
                DB::table('role_menu')->updateOrInsert(
                    ['role_id' => $superadmin->id, 'menu_id' => $menu->id]
                );
            }
        };

        foreach ($bankSantriSubmenus as $submenu) {
            $upsertMenu($bankSantri->id, $submenu);
        }

        foreach ($laporanKeuanganSubmenus as $submenu) {
            $upsertMenu($laporanKeuangan->id, $submenu);
        }
        
        if ($superadmin) {
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $superadmin->id, 'menu_id' => $bankSantri->id]
            );
            DB::table('role_menu')->updateOrInsert(
                ['role_id' => $superadmin->id, 'menu_id' => $laporanKeuangan->id]
            );
        }
    }
}
