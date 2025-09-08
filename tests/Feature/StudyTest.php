<?php

namespace Tests\Feature;

use App\Models\Study;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_studies()
    {
        Study::factory()->count(3)->create();

        $response = $this->getJson('/api/master/study');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => true
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_study()
    {
        $data = [
            'name' => 'Studi Ilmu Komputer',
            'description' => 'Studi yang fokus pada ilmu komputer dan teknologi informasi'
        ];

        $response = $this->postJson('/api/master/study', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Studi berhasil dibuat',
                'data' => [
                    'name' => 'Studi Ilmu Komputer',
                    'description' => 'Studi yang fokus pada ilmu komputer dan teknologi informasi'
                ]
            ]);

        $this->assertDatabaseHas('studies', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_a_study()
    {
        $response = $this->postJson('/api/master/study', [
            'description' => 'No name provided'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['name']
            ]);
    }

    /** @test */
    public function it_can_show_a_study()
    {
        $study = Study::factory()->create([
            'name' => 'Studi Matematika',
            'description' => 'Studi yang fokus pada ilmu matematika'
        ]);

        $response = $this->getJson("/api/master/study/{$study->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Studi Matematika',
                    'description' => 'Studi yang fokus pada ilmu matematika'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_study()
    {
        $response = $this->getJson('/api/master/study/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Studi tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_study()
    {
        $study = Study::factory()->create([
            'name' => 'Studi Fisika',
            'description' => 'Studi yang fokus pada ilmu fisika'
        ]);

        $updatedData = [
            'name' => 'Studi Fisika Terapan',
            'description' => 'Studi yang fokus pada penerapan ilmu fisika'
        ];

        $response = $this->putJson("/api/master/study/{$study->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Studi berhasil diperbarui',
                'data' => [
                    'name' => 'Studi Fisika Terapan',
                    'description' => 'Studi yang fokus pada penerapan ilmu fisika'
                ]
            ]);

        $this->assertDatabaseHas('studies', $updatedData);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_study()
    {
        $response = $this->putJson('/api/master/study/999', [
            'name' => 'Nonexistent'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Studi tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_study()
    {
        $study = Study::factory()->create();

        $response = $this->deleteJson("/api/master/study/{$study->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Studi berhasil dihapus'
            ]);

        $this->assertSoftDeleted('studies', ['id' => $study->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_study()
    {
        $response = $this->deleteJson('/api/master/study/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Studi tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_study()
    {
        // Membuat study dan menghapusnya
        $study = Study::factory()->create();
        $study->delete();

        // Memastikan study dalam keadaan terhapus
        $this->assertSoftDeleted('studies', ['id' => $study->id]);

        // Memulihkan study
        $response = $this->postJson("/api/master/study/{$study->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Studi berhasil dipulihkan',
                'data' => [
                    'id' => $study->id,
                    'name' => $study->name,
                    'description' => $study->description
                ]
            ]);

        // Memastikan study telah dipulihkan
        $this->assertDatabaseHas('studies', [
            'id' => $study->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_study()
    {
        $response = $this->postJson('/api/master/study/999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Studi tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_study()
    {
        // Membuat study yang tidak dihapus
        $study = Study::factory()->create();

        // Mencoba memulihkan study yang tidak dihapus
        $response = $this->postJson("/api/master/study/{$study->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Studi tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_studies()
    {
        // Membuat study dan menghapus beberapa
        $studies = Study::factory()->count(3)->create();
        $studies[0]->delete();
        $studies[2]->delete();

        // Mengambil daftar study yang terhapus
        $response = $this->getJson('/api/master/study/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => true
            ])
            ->assertJsonCount(2, 'data');
    }
}
