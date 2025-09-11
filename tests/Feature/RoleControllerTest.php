<?php

namespace Tests\Feature;

use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_roles()
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $response = $this->getJson('/api/main/role');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data peran berhasil diambil'
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_create_role()
    {
        $data = [
            'name' => 'admin',
            'guard_name' => 'api'
        ];

        $response = $this->postJson('/api/main/role', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Peran berhasil dibuat'
            ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'guard_name' => 'api'
        ]);
    }

    /** @test */
    public function it_can_show_role()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->getJson("/api/main/role/{$role->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data peran berhasil diambil',
                'data' => [
                    'id' => $role->id,
                    'name' => 'admin'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_role()
    {
        $response = $this->getJson('/api/main/role/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Peran tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_role()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $updatedData = [
            'name' => 'administrator',
            'guard_name' => 'api'
        ];

        $response = $this->putJson("/api/main/role/{$role->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Peran berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'administrator',
            'guard_name' => 'api'
        ]);
    }

    /** @test */
    public function it_can_delete_role()
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $response = $this->deleteJson("/api/main/role/{$role->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Peran berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /** @test */
    public function it_can_assign_permissions_to_role()
    {
        // Create permissions
        Permission::create(['name' => 'create-user', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit-user', 'guard_name' => 'api']);

        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        $data = [
            'permissions' => ['create-user', 'edit-user']
        ];

        $response = $this->postJson("/api/main/role/{$role->id}/assign-permissions", $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Izin berhasil ditetapkan ke peran'
            ]);

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('create-user'));
        $this->assertTrue($role->hasPermissionTo('edit-user'));
    }

    /** @test */
    public function it_can_remove_permissions_from_role()
    {
        // Create permissions
        Permission::create(['name' => 'create-user', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit-user', 'guard_name' => 'api']);

        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);

        // Assign permissions first
        $role->givePermissionTo(['create-user', 'edit-user']);
        $this->assertTrue($role->hasPermissionTo('create-user'));
        $this->assertTrue($role->hasPermissionTo('edit-user'));

        $data = [
            'permissions' => ['create-user']
        ];

        $response = $this->postJson("/api/main/role/{$role->id}/remove-permissions", $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Izin berhasil dihapus dari peran'
            ]);

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo('create-user'));
        $this->assertTrue($role->hasPermissionTo('edit-user'));
    }
}
