<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ParentProfile;
use App\Models\Registration;
use App\Models\Program;
use App\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $parentUser1;
    protected $parentProfile1;
    protected $parentUser2;
    protected $parentProfile2;
    protected $reg1;
    protected $reg2;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup academic year
        AcademicYear::create([
            'year' => date('Y'),
            'start_date' => date('Y') . '-01-01',
            'end_date' => date('Y') . '-12-31',
            'active' => true,
        ]);

        // Setup roles
        Role::create(['name' => 'orangtua', 'guard_name' => 'api']);

        // Create Admin user (no role 'orangtua')
        $this->adminUser = User::factory()->create();

        // Create Parent 1
        $this->parentUser1 = User::factory()->create();
        $this->parentUser1->assignRole('orangtua');
        $this->parentProfile1 = ParentProfile::create([
            'first_name' => 'Parent',
            'last_name' => 'One',
            'nik' => '1111111111111111',
            'kk' => '1111111111111111',
            'gender' => 'L',
            'parent_as' => 'ayah',
            'user_id' => $this->parentUser1->id,
        ]);

        // Create Parent 2
        $this->parentUser2 = User::factory()->create();
        $this->parentUser2->assignRole('orangtua');
        $this->parentProfile2 = ParentProfile::create([
            'first_name' => 'Parent',
            'last_name' => 'Two',
            'nik' => '2222222222222222',
            'kk' => '2222222222222222',
            'gender' => 'L',
            'parent_as' => 'ayah',
            'user_id' => $this->parentUser2->id,
        ]);

        // Create registrations
        $this->reg1 = Registration::create([
            'registration_number' => 'REG' . date('Y') . '001',
            'parent_id' => '1111111111111111',
            'nis' => '1111111111',
            'first_name' => 'Child',
            'last_name' => 'One',
            'nik' => '1111111111111112',
            'kk' => '1111111111111111',
            'gender' => 'L',
            'address' => 'Address 1',
            'born_in' => 'City 1',
            'born_at' => '2015-01-01',
            'status' => 'pending',
        ]);

        $this->reg2 = Registration::create([
            'registration_number' => 'REG' . date('Y') . '002',
            'parent_id' => '2222222222222222',
            'nis' => '2222222222',
            'first_name' => 'Child',
            'last_name' => 'Two',
            'nik' => '2222222222222223',
            'kk' => '2222222222222222',
            'gender' => 'P',
            'address' => 'Address 2',
            'born_in' => 'City 2',
            'born_at' => '2016-01-01',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function admin_can_list_all_registrations()
    {
        $this->actingAs($this->adminUser, 'api');

        $response = $this->getJson('/api/main/registration');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    /** @test */
    public function parent_can_only_list_their_own_registrations()
    {
        $this->actingAs($this->parentUser1, 'api');

        $response = $this->getJson('/api/main/registration');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.parent_id', '1111111111111111');
    }

    /** @test */
    public function admin_can_list_all_current_year_registrations()
    {
        $this->actingAs($this->adminUser, 'api');

        $response = $this->getJson('/api/main/registration/current-year');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    /** @test */
    public function parent_can_only_list_their_own_current_year_registrations()
    {
        $this->actingAs($this->parentUser2, 'api');

        $response = $this->getJson('/api/main/registration/current-year');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.parent_id', '2222222222222222');
    }

    /** @test */
    public function admin_can_show_any_registration()
    {
        $this->actingAs($this->adminUser, 'api');

        $response = $this->getJson("/api/main/registration/{$this->reg1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.parent_id', '1111111111111111');
    }

    /** @test */
    public function parent_can_show_their_own_registration()
    {
        $this->actingAs($this->parentUser1, 'api');

        $response = $this->getJson("/api/main/registration/{$this->reg1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.parent_id', '1111111111111111');
    }

    /** @test */
    public function parent_cannot_show_other_registration()
    {
        $this->actingAs($this->parentUser1, 'api');

        $response = $this->getJson("/api/main/registration/{$this->reg2->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_any_registration()
    {
        $this->actingAs($this->adminUser, 'api');

        $response = $this->putJson("/api/main/registration/{$this->reg1->id}", [
            'santri_nama_depan' => 'Child One Updated',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('registrations', [
            'id' => $this->reg1->id,
            'first_name' => 'Child One Updated',
        ]);
    }

    /** @test */
    public function parent_can_update_their_own_registration()
    {
        $this->actingAs($this->parentUser1, 'api');

        $response = $this->putJson("/api/main/registration/{$this->reg1->id}", [
            'santri_nama_depan' => 'Child One Updated Parent',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('registrations', [
            'id' => $this->reg1->id,
            'first_name' => 'Child One Updated Parent',
        ]);
    }

    /** @test */
    public function parent_cannot_update_other_registration()
    {
        $this->actingAs($this->parentUser1, 'api');

        $response = $this->putJson("/api/main/registration/{$this->reg2->id}", [
            'santri_nama_depan' => 'Hack Attempt',
        ]);

        $response->assertStatus(403);
    }
}
