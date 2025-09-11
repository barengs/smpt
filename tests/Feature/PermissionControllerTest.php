<?php

namespace Tests\Feature;

use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_permissions()
    {
        Permission::create(['name' => 'create-user', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit-user', 'guard_name' => 'api']);

        $response = $this->getJson('/api/main/permission');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data izin berhasil diambil'
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_create_permission()
    {
        $data = [
            'name' => 'create-user',
            'guard_name' => 'api'
        ];

        $response = $this->postJson('/api/main/permission', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Izin berhasil dibuat'
            ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'create-user',
            'guard_name' => 'api'
        ]);
    }

    /** @test */
    public function it_can_show_permission()
    {
        $permission = Permission::create(['name' => 'create-user', 'guard_name' => 'api']);

        $response = $this->getJson("/api/main/permission/{$permission->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data izin berhasil diambil',
                'data' => [
                    'id' => $permission->id,
                    'name' => 'create-user'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_permission()
    {
        $response = $this->getJson('/api/main/permission/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Izin tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_permission()
    {
        $permission = Permission::create(['name' => 'create-user', 'guard_name' => 'api']);

        $updatedData = [
            'name' => 'manage-user',
            'guard_name' => 'api'
        ];

        $response = $this->putJson("/api/main/permission/{$permission->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Izin berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'manage-user',
            'guard_name' => 'api'
        ]);
    }

    /** @test */
    public function it_can_delete_permission()
    {
        $permission = Permission::create(['name' => 'create-user', 'guard_name' => 'api']);

        $response = $this->deleteJson("/api/main/permission/{$permission->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Izin berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    /** @test */
    public function it_can_assign_roles_to_permission()
    {
        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $permission = Permission::create(['name' => 'create-user', 'guard_name' => 'api']);

        $data = [
            'roles' => ['admin', 'editor']
        ];

        $response = $this->postJson("/api/main/permission/{$permission->id}/assign-roles", $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Peran berhasil ditetapkan ke izin'
            ]);

        $permission->refresh();
        $this->assertTrue($permission->hasRole('admin'));
        $this->assertTrue($permission->hasRole('editor'));
    }

    /** @test */
    public function it_can_remove_roles_from_permission()
    {
        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'editor', 'guard_name' => 'api']);

        $permission = Permission::create(['name' => 'create-user', 'guard_name' => 'api']);

        // Assign roles first
        $permission->assignRole(['admin', 'editor']);
        $this->assertTrue($permission->hasRole('admin'));
        $this->assertTrue($permission->hasRole('editor'));

        $data = [
            'roles' => ['admin']
        ];

        $response = $this->postJson("/api/main/permission/{$permission->id}/remove-roles", $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Peran berhasil dihapus dari izin'
            ]);

        $permission->refresh();
        $this->assertFalse($permission->hasRole('admin'));
        $this->assertTrue($permission->hasRole('editor'));
    }
}
