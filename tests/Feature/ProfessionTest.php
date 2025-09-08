<?php

namespace Tests\Feature;

use App\Models\Profession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_professions()
    {
        Profession::factory()->count(3)->create();

        $response = $this->getJson('/api/master/profession');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => true
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_profession()
    {
        $data = [
            'name' => 'Software Engineer',
            'description' => 'Develops software applications'
        ];

        $response = $this->postJson('/api/master/profession', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Profesi berhasil dibuat',
                'data' => [
                    'name' => 'Software Engineer',
                    'description' => 'Develops software applications'
                ]
            ]);

        $this->assertDatabaseHas('professions', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_a_profession()
    {
        $response = $this->postJson('/api/master/profession', [
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
    public function it_can_show_a_profession()
    {
        $profession = Profession::factory()->create([
            'name' => 'Doctor',
            'description' => 'Medical professional'
        ]);

        $response = $this->getJson("/api/master/profession/{$profession->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Doctor',
                    'description' => 'Medical professional'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_profession()
    {
        $response = $this->getJson('/api/master/profession/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Profesi tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_profession()
    {
        $profession = Profession::factory()->create([
            'name' => 'Teacher',
            'description' => 'Educates students'
        ]);

        $updatedData = [
            'name' => 'Senior Teacher',
            'description' => 'Educates students with more experience'
        ];

        $response = $this->putJson("/api/master/profession/{$profession->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profesi berhasil diperbarui',
                'data' => [
                    'name' => 'Senior Teacher',
                    'description' => 'Educates students with more experience'
                ]
            ]);

        $this->assertDatabaseHas('professions', $updatedData);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_profession()
    {
        $response = $this->putJson('/api/master/profession/999', [
            'name' => 'Nonexistent'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Profesi tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_profession()
    {
        $profession = Profession::factory()->create();

        $response = $this->deleteJson("/api/master/profession/{$profession->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profesi berhasil dihapus'
            ]);

        $this->assertSoftDeleted('professions', ['id' => $profession->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_profession()
    {
        $response = $this->deleteJson('/api/master/profession/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Profesi tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_profession()
    {
        // Membuat profesi dan menghapusnya
        $profession = Profession::factory()->create();
        $profession->delete();

        // Memastikan profesi dalam keadaan terhapus
        $this->assertSoftDeleted('professions', ['id' => $profession->id]);

        // Memulihkan profesi
        $response = $this->postJson("/api/master/profession/{$profession->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profesi berhasil dipulihkan',
                'data' => [
                    'id' => $profession->id,
                    'name' => $profession->name,
                    'description' => $profession->description
                ]
            ]);

        // Memastikan profesi telah dipulihkan
        $this->assertDatabaseHas('professions', [
            'id' => $profession->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_profession()
    {
        $response = $this->postJson('/api/master/profession/999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Profesi tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_profession()
    {
        // Membuat profesi yang tidak dihapus
        $profession = Profession::factory()->create();

        // Mencoba memulihkan profesi yang tidak dihapus
        $response = $this->postJson("/api/master/profession/{$profession->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Profesi tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_professions()
    {
        // Membuat profesi dan menghapus beberapa
        $professions = Profession::factory()->count(3)->create();
        $professions[0]->delete();
        $professions[2]->delete();

        // Mengambil daftar profesi yang terhapus
        $response = $this->getJson('/api/master/profession/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => true
            ])
            ->assertJsonCount(2, 'data');
    }
}
