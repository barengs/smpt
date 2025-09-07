<?php

namespace Tests\Feature;

use App\Models\Education;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationTest extends TestCase
{
    use RefreshDatabase;

    protected $education;

    protected function setUp(): void
    {
        parent::setUp();
        $this->education = Education::factory()->create();
    }

    /** @test */
    public function it_can_list_all_education()
    {
        Education::factory()->count(3)->create();

        $response = $this->getJson('/api/master/education');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pendidikan berhasil diambil'
            ])
            ->assertJsonCount(4, 'data.data');
    }

    /** @test */
    public function it_can_create_a_education()
    {
        $data = [
            'name' => 'Sekolah Dasar',
            'description' => 'Jenjang pendidikan dasar'
        ];

        $response = $this->postJson('/api/master/education', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('education', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_a_education()
    {
        $response = $this->postJson('/api/master/education', [
            'description' => 'No name provided'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_show_a_education()
    {
        $response = $this->getJson("/api/master/education/{$this->education->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pendidikan berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_education()
    {
        $response = $this->getJson('/api/master/education/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_education()
    {
        $data = [
            'name' => 'Sekolah Menengah Pertama',
            'description' => 'Jenjang pendidikan menengah pertama'
        ];

        $response = $this->putJson("/api/master/education/{$this->education->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('education', $data);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_education()
    {
        $data = [
            'name' => 'Sekolah Menengah Atas',
            'description' => 'Jenjang pendidikan menengah atas'
        ];

        $response = $this->putJson('/api/master/education/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_education()
    {
        $response = $this->deleteJson("/api/master/education/{$this->education->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil dihapus'
            ]);

        $this->assertSoftDeleted('education', ['id' => $this->education->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_education()
    {
        $response = $this->deleteJson('/api/master/education/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_education()
    {
        $this->education->delete();

        $response = $this->postJson("/api/master/education/{$this->education->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil dipulihkan'
            ]);

        $this->assertDatabaseHas('education', [
            'id' => $this->education->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_education()
    {
        $response = $this->postJson('/api/master/education/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_education()
    {
        // Mencoba memulihkan pendidikan yang tidak dihapus
        $response = $this->postJson("/api/master/education/{$this->education->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_education()
    {
        $this->education->delete();

        $response = $this->getJson('/api/master/education/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pendidikan terhapus berhasil diambil'
            ]);
    }
}
