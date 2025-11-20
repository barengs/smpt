<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Staff;
use Spatie\Permission\Models\Role;

class StaffRoleUpdateTest extends TestCase
{
  use RefreshDatabase;

  protected $user;
  protected $staff;
  protected $role1;
  protected $role2;

  protected function setUp(): void
  {
    parent::setUp();

    // Create roles
    $this->role1 = Role::create(['name' => 'staff']);
    $this->role2 = Role::create(['name' => 'admin']);

    // Create a user with role1
    $this->user = User::factory()->create();
    $this->user->assignRole($this->role1);

    // Create a staff member linked to the user
    $this->staff = Staff::factory()->create([
      'user_id' => $this->user->id,
      'status' => 'Aktif'
    ]);
  }

  /**
   * Test updating staff roles
   *
   * @return void
   */
  public function test_can_update_staff_roles()
  {
    $this->assertTrue($this->user->hasRole('staff'));
    $this->assertFalse($this->user->hasRole('admin'));

    $response = $this->putJson("/api/staff/{$this->staff->id}", [
      'first_name' => $this->staff->first_name,
      'last_name' => $this->staff->last_name,
      'roles' => ['admin']
    ]);

    $response->assertStatus(200);

    // Refresh user to get updated roles
    $this->user->refresh();

    $this->assertFalse($this->user->hasRole('staff'));
    $this->assertTrue($this->user->hasRole('admin'));
  }

  /**
   * Test updating staff roles with multiple roles
   *
   * @return void
   */
  public function test_can_update_staff_with_multiple_roles()
  {
    $response = $this->putJson("/api/staff/{$this->staff->id}", [
      'first_name' => $this->staff->first_name,
      'last_name' => $this->staff->last_name,
      'roles' => ['staff', 'admin']
    ]);

    $response->assertStatus(200);

    $this->user->refresh();

    $this->assertTrue($this->user->hasRole('staff'));
    $this->assertTrue($this->user->hasRole('admin'));
  }
}
