<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restore non-education menus that were accidentally deleted or altered by
     * hardcoded IDs in 2026_11_03_000001_cleanup_educational_management_tables.
     */
    public function up(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        $now = now();

        // Lookup parent IDs dynamically by title
        $parentIds = [
            'Bank Santri'      => DB::table('menus')->where('id_title', 'Bank Santri')->value('id') ?? 10,
            'Kepesantrenan'    => DB::table('menus')->where('id_title', 'Kepesantrenan')->value('id') ?? 12,
            'Informasi'        => DB::table('menus')->where('id_title', 'Informasi')->value('id') ?? 14,
            'Manajemen Santri' => DB::table('menus')->where('id_title', 'Manajemen Santri')->value('id') ?? 15,
            'Data Master'      => DB::table('menus')->where('id_title', 'Data Master')->value('id') ?? 16,
        ];

        // Menus to restore/repair with their production IDs, routes, parents, roles, and menu_permissions
        $menusToRestore = [
            [
                'id'          => 58,
                'id_title'    => 'Data Santri',
                'en_title'    => 'Student Data',
                'icon'        => 'user-graduate',
                'route'       => '/dashboard/santri',
                'parent_id'   => $parentIds['Manajemen Santri'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 10,
                'roles'       => [1, 2, 3, 4, 9, 10, 11, 12, 13, 14],
                'permissions' => [206, 476, 477, 478, 479],
            ],
            [
                'id'          => 59,
                'id_title'    => 'Pendaftaran Santri',
                'en_title'    => 'Student Registration',
                'icon'        => 'user-plus',
                'route'       => '/dashboard/pendaftaran-santri',
                'parent_id'   => $parentIds['Manajemen Santri'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 11,
                'roles'       => [1, 9, 10],
                'permissions' => [310, 480, 481, 482, 483],
            ],
            [
                'id'          => 47,
                'id_title'    => 'Asrama',
                'en_title'    => 'Hostel',
                'icon'        => 'home',
                'route'       => '/dashboard/kepesantrenan/asrama',
                'parent_id'   => $parentIds['Kepesantrenan'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 20,
                'roles'       => [1, 9, 10, 11, 12],
                'permissions' => [196, 197, 198, 199, 200],
            ],
            [
                'id'          => 55,
                'id_title'    => 'Jadwal Kegiatan',
                'en_title'    => 'Activity Schedule',
                'icon'        => 'clock',
                'route'       => '/dashboard/kepesantrenan/jadwal-kegiatan',
                'parent_id'   => $parentIds['Kepesantrenan'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 22,
                'roles'       => [1, 2, 3, 9, 10],
                'permissions' => [232, 233, 234, 235, 320],
            ],
            [
                'id'          => 49,
                'id_title'    => 'Program',
                'en_title'    => 'Program Education',
                'icon'        => 'bookmark',
                'route'       => '/dashboard/master-data/program',
                'parent_id'   => $parentIds['Data Master'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 35,
                'roles'       => [1, 9],
                'permissions' => [208, 209, 210, 211, 325],
            ],
            [
                'id'          => 50,
                'id_title'    => 'Tahun Ajaran',
                'en_title'    => 'Academic Year',
                'icon'        => 'calendar',
                'route'       => '/dashboard/master-data/tahun-ajaran',
                'parent_id'   => $parentIds['Data Master'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 36,
                'roles'       => [1, 9],
                'permissions' => [212, 213, 214, 215, 326],
            ],
            [
                'id'          => 56,
                'id_title'    => 'Berita',
                'en_title'    => 'News Feed',
                'icon'        => 'newspaper',
                'route'       => '/dashboard/berita',
                'parent_id'   => $parentIds['Informasi'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 57,
                'roles'       => [1, 9],
                'permissions' => [327, 328, 329, 330, 331],
            ],
            [
                'id'          => 57,
                'id_title'    => 'Pengumuman',
                'en_title'    => 'Announcement',
                'icon'        => 'bell',
                'route'       => '/dashboard/pengumuman',
                'parent_id'   => $parentIds['Informasi'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 58,
                'roles'       => [1, 9],
                'permissions' => [332, 333, 334, 335, 336],
            ],
            [
                'id'          => 67,
                'id_title'    => 'Kota',
                'en_title'    => 'Regency',
                'icon'        => 'map-pinned',
                'route'       => '/dashboard/wilayah/kota',
                'parent_id'   => $parentIds['Data Master'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 62,
                'roles'       => [1],
                'permissions' => [498, 499, 500, 501, 502],
            ],
            [
                'id'          => 38,
                'id_title'    => 'Transaksi Bank',
                'en_title'    => 'Bank Transaction',
                'icon'        => 'refresh-cw',
                'route'       => '/dashboard/bank-santri/transaksi',
                'parent_id'   => $parentIds['Bank Santri'],
                'type'        => 'main',
                'position'    => 'sidebar',
                'status'      => 'active',
                'order'       => 18,
                'roles'       => [1],
                'permissions' => [469, 470, 471, 472, 473],
            ],
        ];

        $existingRoleIds = Schema::hasTable('roles')
            ? DB::table('roles')->pluck('id')->toArray()
            : [];
        $existingPermIds = Schema::hasTable('permissions')
            ? DB::table('permissions')->pluck('id')->toArray()
            : [];

        foreach ($menusToRestore as $item) {
            $existingByTitle = DB::table('menus')->where('id_title', $item['id_title'])->first();
            $existingById = DB::table('menus')->where('id', $item['id'])->first();

            $payload = [
                'id_title'   => $item['id_title'],
                'en_title'   => $item['en_title'],
                'icon'       => $item['icon'],
                'route'      => $item['route'],
                'parent_id'  => $item['parent_id'],
                'type'       => $item['type'],
                'position'   => $item['position'],
                'status'     => $item['status'],
                'order'      => $item['order'],
                'updated_at' => $now,
            ];

            if ($existingByTitle) {
                DB::table('menus')->where('id', $existingByTitle->id)->update($payload);
                $menuId = $existingByTitle->id;
            } elseif (!$existingById) {
                $payload['id'] = $item['id'];
                $payload['created_at'] = $now;
                DB::table('menus')->insert($payload);
                $menuId = $item['id'];
            } else {
                $payload['created_at'] = $now;
                $menuId = DB::table('menus')->insertGetId($payload);
            }

            // Restore role_menu associations
            if (Schema::hasTable('role_menu')) {
                foreach ($item['roles'] as $roleId) {
                    if (in_array($roleId, $existingRoleIds)) {
                        DB::table('role_menu')->updateOrInsert(
                            ['role_id' => $roleId, 'menu_id' => $menuId],
                            ['created_at' => $now, 'updated_at' => $now]
                        );
                    }
                }
            }

            // Restore menu_permissions associations
            if (Schema::hasTable('menu_permissions')) {
                foreach ($item['permissions'] as $permId) {
                    if (in_array($permId, $existingPermIds)) {
                        DB::table('menu_permissions')->updateOrInsert(
                            ['menu_id' => $menuId, 'permission_id' => $permId]
                        );
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Corrective migration
    }
};
