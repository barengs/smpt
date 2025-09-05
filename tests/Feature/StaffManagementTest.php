<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Staff;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for testing
        $this->user = User::factory()->create();

        // Create a staff member for testing
        $this->staff = Staff::factory()->create([
            'user_id' => $this->user->id,
            'code' => 'STF001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'status' => 'Aktif'
        ]);
    }

    /**
     * Test listing staff members
     *
     * @return void
     */
    public function test_can_list_staff_members()
    {
        $response = $this->getJson('/api/staff');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'code', 'first_name', 'last_name', 'email', 'status']
                ],
                'links',
                'meta'
            ]);
    }

    /**
     * Test creating a new staff member
     *
     * @return void
     */
    public function test_can_create_staff_member()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/staff', [
            'user_id' => $user->id,
            'code' => 'STF002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'status' => 'Aktif'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'user_id', 'code', 'first_name', 'last_name', 'email', 'status'
            ])
            ->assertJson([
                'code' => 'STF002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'status' => 'Aktif'
            ]);

        $this->assertDatabaseHas('staff', [
            'code' => 'STF002',
            'email' => 'jane.smith@example.com'
        ]);
    }

    /**
     * Test viewing a specific staff member
     *
     * @return void
     */
    public function test_can_view_staff_member()
    {
        $response = $this->getJson("/api/staff/{$this->staff->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'user_id', 'code', 'first_name', 'last_name', 'email', 'status'
            ])
            ->assertJson([
                'id' => $this->staff->id,
                'code' => 'STF001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com'
            ]);
    }

    /**
     * Test updating a staff member
     *
     * @return void
     */
    public function test_can_update_staff_member()
    {
        $response = $this->putJson("/api/staff/{$this->staff->id}", [
            'first_name' => 'Johnny',
            'last_name' => 'Doe Updated',
            'phone' => '1234567890'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'first_name' => 'Johnny',
                'last_name' => 'Doe Updated',
                'phone' => '1234567890'
            ]);

        $this->assertDatabaseHas('staff', [
            'id' => $this->staff->id,
            'first_name' => 'Johnny',
            'last_name' => 'Doe Updated'
        ]);
    }

    /**
     * Test deleting a staff member
     *
     * @return void
     */
    public function test_can_delete_staff_member()
    {
        $response = $this->deleteJson("/api/staff/{$this->staff->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Staff deleted successfully'
            ]);

        $this->assertSoftDeleted('staff', [
            'id' => $this->staff->id
        ]);
    }

    /**
     * Test restoring a deleted staff member
     *
     * @return void
     */
    public function test_can_restore_staff_member()
    {
        // First delete the staff member
        $this->staff->delete();

        $response = $this->postJson("/api/staff/{$this->staff->id}/restore");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'user_id', 'code', 'first_name', 'last_name', 'email', 'status'
            ]);

        $this->assertDatabaseHas('staff', [
            'id' => $this->staff->id,
            'deleted_at' => null
        ]);
    }

    /**
     * Test updating staff status
     *
     * @return void
     */
    public function test_can_update_staff_status()
    {
        $response = $this->putJson("/api/staff/{$this->staff->id}/status", [
            'status' => 'Tidak Aktif'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Staff status updated successfully'
            ]);

        $this->assertDatabaseHas('staff', [
            'id' => $this->staff->id,
            'status' => 'Tidak Aktif'
        ]);
    }

    /**
     * Test validation errors when creating staff
     *
     * @return void
     */
    public function test_staff_creation_validation_errors()
    {
        $response = $this->postJson('/api/staff', [
            'first_name' => '', // Required field
            'email' => 'invalid-email' // Invalid format
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test getting staff by user ID
     *
     * @return void
     */
    public function test_can_get_staff_by_user_id()
    {
        $response = $this->getJson("/api/staff/{$this->staff->user_id}/user");

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $this->staff->user_id,
                'code' => 'STF001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com'
            ]);
    }

    /**
     * Test bulk delete staff members
     *
     * @return void
     */
    public function test_can_bulk_delete_staff_members()
    {
        // Create additional staff members
        $staff1 = Staff::factory()->create(['code' => 'STF003']);
        $staff2 = Staff::factory()->create(['code' => 'STF004']);

        $response = $this->postJson('/api/staff/bulk-delete', [
            'ids' => [$staff1->id, $staff2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => '2 staff members deleted successfully'
            ]);

        $this->assertSoftDeleted('staff', ['id' => $staff1->id]);
        $this->assertSoftDeleted('staff', ['id' => $staff2->id]);
    }

    /**
     * Test bulk restore staff members
     *
     * @return void
     */
    public function test_can_bulk_restore_staff_members()
    {
        // Create and delete staff members
        $staff1 = Staff::factory()->create(['code' => 'STF003']);
        $staff2 = Staff::factory()->create(['code' => 'STF004']);

        $staff1->delete();
        $staff2->delete();

        $response = $this->postJson('/api/staff/bulk-restore', [
            'ids' => [$staff1->id, $staff2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => '2 staff members restored successfully'
            ]);

        $this->assertDatabaseHas('staff', [
            'id' => $staff1->id,
            'deleted_at' => null
        ]);

        $this->assertDatabaseHas('staff', [
            'id' => $staff2->id,
            'deleted_at' => null
        ]);
    }

    /**
     * Test getting trashed staff members
     *
     * @return void
     */
    public function test_can_get_trashed_staff_members()
    {
        // Delete a staff member
        $this->staff->delete();

        $response = $this->getJson('/api/staff/trashed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'code', 'first_name', 'last_name', 'email', 'status', 'deleted_at']
                ],
                'links',
                'meta'
            ]);
    }

    /**
     * Test staff statistics
     *
     * @return void
     */
    public function test_can_get_staff_statistics()
    {
        // Create additional staff members with different statuses
        Staff::factory()->create(['code' => 'STF003', 'status' => 'Aktif']);
        Staff::factory()->create(['code' => 'STF004', 'status' => 'Tidak Aktif']);

        // Delete a staff member
        $this->staff->delete();

        $response = $this->getJson('/api/staff/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'total' => 3,
                'active' => 2,
                'inactive' => 1,
                'trashed' => 1
            ]);
    }

    /**
     * Test force delete staff member
     *
     * @return void
     */
    public function test_can_force_delete_staff_member()
    {
        // Delete a staff member first
        $this->staff->delete();

        $response = $this->deleteJson("/api/staff/{$this->staff->id}/force-delete");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Staff permanently deleted successfully'
            ]);

        $this->assertDatabaseMissing('staff', [
            'id' => $this->staff->id
        ]);
    }

    /**
     * Test bulk force delete staff members
     *
     * @return void
     */
    public function test_can_bulk_force_delete_staff_members()
    {
        // Create and delete staff members
        $staff1 = Staff::factory()->create(['code' => 'STF003']);
        $staff2 = Staff::factory()->create(['code' => 'STF004']);

        $staff1->delete();
        $staff2->delete();

        $response = $this->postJson('/api/staff/bulk-force-delete', [
            'ids' => [$staff1->id, $staff2->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => '2 staff members permanently deleted successfully'
            ]);

        $this->assertDatabaseMissing('staff', ['id' => $staff1->id]);
        $this->assertDatabaseMissing('staff', ['id' => $staff2->id]);
    }

    /**
     * Test that a user can only have one staff record
     *
     * @return void
     */
    public function test_user_can_only_have_one_staff_record()
    {
        // Try to create another staff record for the same user
        $response = $this->postJson('/api/staff', [
            'user_id' => $this->user->id,
            'code' => 'STF002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'status' => 'Aktif'
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'User already has a staff record'
            ]);
    }
}
