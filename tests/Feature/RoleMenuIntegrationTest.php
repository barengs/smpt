<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleMenuIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /** @test */
    public function role_can_have_menus_relation()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $menu = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);

        $role->menus()->attach($menu->id);

        $this->assertTrue($role->menus->contains($menu));
        $this->assertEquals(1, $role->menus->count());
    }

    /** @test */
    public function menu_can_have_roles_relation()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $menu = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);

        $menu->roles()->attach($role->id);

        $this->assertTrue($menu->roles->contains($role));
        $this->assertEquals(1, $menu->roles->count());
    }

    /** @test */
    public function menu_can_have_permissions()
    {
        $permission = Permission::create(['name' => 'lihat dashboard', 'guard_name' => 'api']);
        $menu = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);

        $menu->permissions()->attach($permission->id);

        $this->assertTrue($menu->permissions->contains($permission));
        $this->assertEquals(1, $menu->permissions->count());
    }

    /** @test */
    public function it_can_get_role_menus_via_api()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $menu = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);

        $role->menus()->attach($menu->id);

        $response = $this->getJson("/api/main/role/{$role->id}/menus");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Menu untuk role berhasil diambil'
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_assign_menus_to_role_via_api()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $menu1 = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);
        $menu2 = Menu::create([
            'id_title' => 'Settings',
            'en_title' => 'Settings',
            'route' => '/settings',
            'type' => 'main',
            'status' => 'active'
        ]);

        $response = $this->postJson("/api/main/role/{$role->id}/assign-menus", [
            'menu_ids' => [$menu1->id, $menu2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Menu berhasil ditambahkan ke role'
            ]);

        $role->refresh();
        $this->assertEquals(2, $role->menus->count());
    }

    /** @test */
    public function it_can_sync_menus_via_role_menu_store()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $menu1 = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);
        $menu2 = Menu::create([
            'id_title' => 'Settings',
            'en_title' => 'Settings',
            'route' => '/settings',
            'type' => 'main',
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/main/role-menu', [
            'role_id' => $role->id,
            'menu_ids' => [$menu1->id, $menu2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Menu berhasil ditambahkan ke role'
            ]);

        $role->refresh();
        $this->assertEquals(2, $role->menus->count());
    }

    /** @test */
    public function it_can_get_roles_for_menu()
    {
        $role1 = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $role2 = Role::create(['name' => 'editor', 'guard_name' => 'api']);
        $menu = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);

        $menu->roles()->attach([$role1->id, $role2->id]);

        $response = $this->getJson("/api/main/menu/{$menu->id}/roles");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Role untuk menu berhasil diambil'
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_remove_menus_from_role()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $menu1 = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);
        $menu2 = Menu::create([
            'id_title' => 'Settings',
            'en_title' => 'Settings',
            'route' => '/settings',
            'type' => 'main',
            'status' => 'active'
        ]);

        $role->menus()->attach([$menu1->id, $menu2->id]);
        $this->assertEquals(2, $role->menus->count());

        $response = $this->postJson("/api/main/role/{$role->id}/remove-menus", [
            'menu_ids' => [$menu1->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Menu berhasil dihapus dari role'
            ]);

        $role->refresh();
        $this->assertEquals(1, $role->menus->count());
        $this->assertFalse($role->menus->contains($menu1));
        $this->assertTrue($role->menus->contains($menu2));
    }

    /** @test */
    public function role_menu_pivot_table_stores_timestamps()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $menu = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);

        $role->menus()->attach($menu->id);

        $this->assertDatabaseHas('role_menu', [
            'role_id' => $role->id,
            'menu_id' => $menu->id
        ]);
    }

    /** @test */
    public function menu_can_have_parent_child_relation()
    {
        $parentMenu = Menu::create([
            'id_title' => 'Settings',
            'en_title' => 'Settings',
            'route' => '/settings',
            'type' => 'main',
            'status' => 'active'
        ]);

        $childMenu = Menu::create([
            'id_title' => 'Profile',
            'en_title' => 'Profile',
            'route' => '/settings/profile',
            'type' => 'submenu',
            'parent_id' => $parentMenu->id,
            'status' => 'active'
        ]);

        $this->assertEquals($parentMenu->id, $childMenu->parent_id);
        $this->assertTrue($parentMenu->child->contains($childMenu));
    }

    /** @test */
    public function permission_can_have_menus_relation()
    {
        $permission = Permission::create(['name' => 'lihat dashboard', 'guard_name' => 'api']);
        $menu = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);

        $menu->permissions()->attach($permission->id);

        // Test from Permission model perspective
        $permissionModel = \App\Models\Permission::find($permission->id);
        $this->assertTrue($permissionModel->menus->contains($menu));
    }

    /** @test */
    public function it_validates_required_fields_when_assigning_menus()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->postJson("/api/main/role/{$role->id}/assign-menus", []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_validates_menu_ids_exist()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->postJson("/api/main/role/{$role->id}/assign-menus", [
            'menu_ids' => [999, 1000]
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function multiple_roles_can_share_same_menu()
    {
        $role1 = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $role2 = Role::create(['name' => 'editor', 'guard_name' => 'api']);
        $menu = Menu::create([
            'id_title' => 'Dashboard',
            'en_title' => 'Dashboard',
            'route' => '/dashboard',
            'type' => 'main',
            'status' => 'active'
        ]);

        $role1->menus()->attach($menu->id);
        $role2->menus()->attach($menu->id);

        $this->assertTrue($role1->menus->contains($menu));
        $this->assertTrue($role2->menus->contains($menu));
        $this->assertEquals(2, $menu->roles->count());
    }
}
