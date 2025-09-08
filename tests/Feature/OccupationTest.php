<?php

namespace Tests\Feature;

use App\Models\Occupation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccupationTest extends TestCase
{
    use RefreshDatabase;

    protected $occupation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->occupation = Occupation::factory()->create();
    }

    /** @test */
    public function it_can_list_all_occupations()
    {
        Occupation::factory()->count(3)->create();

        $response = $this->getJson('/api/master/occupation');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pekerjaan berhasil diambil'
            ])
            ->assertJsonCount(4, 'data.data');
    }

    /** @test */
    public function it_can_create_a_occupation()
    {
        $data = [
            'code' => 'DEV001',
            'name' => 'Software Developer',
            'description' => 'Develops software applications'
        ];

        $response = $this->postJson('/api/master/occupation', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pekerjaan berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('occupations', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_a_occupation()
    {
        $response = $this->postJson('/api/master/occupation', [
            'code' => 'TEST001',
            'description' => 'Test occupation without name'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_ensures_name_is_unique_when_creating_occupation()
    {
        $existingOccupation = Occupation::factory()->create(['name' => 'Manager']);

        $response = $this->postJson('/api/master/occupation', [
            'code' => 'MGR001',
            'name' => 'Manager', // Same name as existing
            'description' => 'Management position'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_show_a_occupation()
    {
        $response = $this->getJson("/api/master/occupation/{$this->occupation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pekerjaan berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_occupation()
    {
        $response = $this->getJson('/api/master/occupation/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pekerjaan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_occupation()
    {
        $data = [
            'code' => 'SD001',
            'name' => 'Senior Developer',
            'description' => 'Senior software developer'
        ];

        $response = $this->putJson("/api/master/occupation/{$this->occupation->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pekerjaan berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('occupations', $data);
    }

    /** @test */
    public function it_ensures_name_is_unique_when_updating_occupation()
    {
        $occupation1 = Occupation::factory()->create(['name' => 'Designer']);
        $occupation2 = Occupation::factory()->create(['name' => 'Developer']);

        // Try to update occupation2 with occupation1's name
        $response = $this->putJson("/api/master/occupation/{$occupation2->id}", [
            'code' => 'DSGN001',
            'name' => 'Designer', // Same name as occupation1
            'description' => 'Graphic Designer'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_occupation()
    {
        $data = [
            'code' => 'QA001',
            'name' => 'Quality Assurance',
            'description' => 'QA specialist'
        ];

        $response = $this->putJson('/api/master/occupation/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pekerjaan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_occupation()
    {
        $response = $this->deleteJson("/api/master/occupation/{$this->occupation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pekerjaan berhasil dihapus'
            ]);

        $this->assertSoftDeleted('occupations', ['id' => $this->occupation->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_occupation()
    {
        $response = $this->deleteJson('/api/master/occupation/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pekerjaan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_occupation()
    {
        $this->occupation->delete();

        $response = $this->postJson("/api/master/occupation/{$this->occupation->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pekerjaan berhasil dipulihkan'
            ]);

        $this->assertDatabaseHas('occupations', [
            'id' => $this->occupation->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_occupation()
    {
        $response = $this->postJson('/api/master/occupation/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pekerjaan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_occupation()
    {
        // Mencoba memulihkan pekerjaan yang tidak dihapus
        $response = $this->postJson("/api/master/occupation/{$this->occupation->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Pekerjaan tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_occupations()
    {
        $this->occupation->delete();

        $response = $this->getJson('/api/master/occupation/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pekerjaan terhapus berhasil diambil'
            ]);
    }
}
