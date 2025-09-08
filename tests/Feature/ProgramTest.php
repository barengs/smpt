<?php

namespace Tests\Feature;

use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_programs()
    {
        Program::factory()->count(3)->create();

        $response = $this->getJson('/api/master/program');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => true
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_program()
    {
        $data = [
            'name' => 'Program Studi Informatika',
            'description' => 'Program studi yang fokus pada ilmu komputer dan teknologi informasi'
        ];

        $response = $this->postJson('/api/master/program', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Program berhasil dibuat',
                'data' => [
                    'name' => 'Program Studi Informatika',
                    'description' => 'Program studi yang fokus pada ilmu komputer dan teknologi informasi'
                ]
            ]);

        $this->assertDatabaseHas('programs', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_a_program()
    {
        $response = $this->postJson('/api/master/program', [
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
    public function it_can_show_a_program()
    {
        $program = Program::factory()->create([
            'name' => 'Program Studi Matematika',
            'description' => 'Program studi yang fokus pada ilmu matematika'
        ]);

        $response = $this->getJson("/api/master/program/{$program->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Program Studi Matematika',
                    'description' => 'Program studi yang fokus pada ilmu matematika'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_program()
    {
        $response = $this->getJson('/api/master/program/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Program tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_program()
    {
        $program = Program::factory()->create([
            'name' => 'Program Studi Fisika',
            'description' => 'Program studi yang fokus pada ilmu fisika'
        ]);

        $updatedData = [
            'name' => 'Program Studi Fisika Terapan',
            'description' => 'Program studi yang fokus pada penerapan ilmu fisika'
        ];

        $response = $this->putJson("/api/master/program/{$program->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Program berhasil diperbarui',
                'data' => [
                    'name' => 'Program Studi Fisika Terapan',
                    'description' => 'Program studi yang fokus pada penerapan ilmu fisika'
                ]
            ]);

        $this->assertDatabaseHas('programs', $updatedData);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_program()
    {
        $response = $this->putJson('/api/master/program/999', [
            'name' => 'Nonexistent'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Program tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_program()
    {
        $program = Program::factory()->create();

        $response = $this->deleteJson("/api/master/program/{$program->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Program berhasil dihapus'
            ]);

        $this->assertSoftDeleted('programs', ['id' => $program->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_program()
    {
        $response = $this->deleteJson('/api/master/program/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Program tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_program()
    {
        // Membuat program dan menghapusnya
        $program = Program::factory()->create();
        $program->delete();

        // Memastikan program dalam keadaan terhapus
        $this->assertSoftDeleted('programs', ['id' => $program->id]);

        // Memulihkan program
        $response = $this->postJson("/api/master/program/{$program->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Program berhasil dipulihkan',
                'data' => [
                    'id' => $program->id,
                    'name' => $program->name,
                    'description' => $program->description
                ]
            ]);

        // Memastikan program telah dipulihkan
        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_program()
    {
        $response = $this->postJson('/api/master/program/999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Program tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_program()
    {
        // Membuat program yang tidak dihapus
        $program = Program::factory()->create();

        // Mencoba memulihkan program yang tidak dihapus
        $response = $this->postJson("/api/master/program/{$program->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Program tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_programs()
    {
        // Membuat program dan menghapus beberapa
        $programs = Program::factory()->count(3)->create();
        $programs[0]->delete();
        $programs[2]->delete();

        // Mengambil daftar program yang terhapus
        $response = $this->getJson('/api/master/program/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => true
            ])
            ->assertJsonCount(2, 'data');
    }
}
