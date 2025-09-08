<?php

namespace Tests\Feature;

use App\Models\Employment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmploymentTest extends TestCase
{
    use RefreshDatabase;

    protected $employment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employment = Employment::factory()->create();
    }

    /** @test */
    public function it_can_list_all_employments()
    {
        Employment::factory()->count(3)->create();

        $response = $this->getJson('/api/master/employment');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pekerjaan berhasil diambil'
            ])
            ->assertJsonCount(4, 'data.data');
    }

    /** @test */
    public function it_can_create_an_employment()
    {
        $data = [
            'name' => 'Software Engineer'
        ];

        $response = $this->postJson('/api/master/employment', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Jenis pekerjaan berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('employments', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_an_employment()
    {
        $response = $this->postJson('/api/master/employment', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_show_an_employment()
    {
        $response = $this->getJson("/api/master/employment/{$this->employment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data jenis pekerjaan berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_employment()
    {
        $response = $this->getJson('/api/master/employment/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Jenis pekerjaan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_an_employment()
    {
        $data = [
            'name' => 'Senior Software Engineer'
        ];

        $response = $this->putJson("/api/master/employment/{$this->employment->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Jenis pekerjaan berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('employments', $data);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_employment()
    {
        $data = [
            'name' => 'Product Manager'
        ];

        $response = $this->putJson('/api/master/employment/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Jenis pekerjaan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_an_employment()
    {
        $response = $this->deleteJson("/api/master/employment/{$this->employment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Jenis pekerjaan berhasil dihapus'
            ]);

        $this->assertSoftDeleted('employments', ['id' => $this->employment->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_employment()
    {
        $response = $this->deleteJson('/api/master/employment/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Jenis pekerjaan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_employment()
    {
        $this->employment->delete();

        $response = $this->postJson("/api/master/employment/{$this->employment->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Jenis pekerjaan berhasil dipulihkan'
            ]);

        $this->assertDatabaseHas('employments', [
            'id' => $this->employment->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_employment()
    {
        $response = $this->postJson('/api/master/employment/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Jenis pekerjaan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_employment()
    {
        // Mencoba memulihkan pekerjaan yang tidak dihapus
        $response = $this->postJson("/api/master/employment/{$this->employment->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Jenis pekerjaan tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_employments()
    {
        $this->employment->delete();

        $response = $this->getJson('/api/master/employment/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data jenis pekerjaan terhapus berhasil diambil'
            ]);
    }
}
