<?php

namespace Tests\Feature;

use App\Models\InternshipSupervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternshipSupervisorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_internship_supervisors()
    {
        InternshipSupervisor::factory()->count(3)->create();

        $response = $this->getJson('/api/master/internship-supervisor');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'address', 'created_at', 'updated_at']
                ]
            ]);
    }

    /** @test */
    public function it_can_create_an_internship_supervisor()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 123'
        ];

        $response = $this->postJson('/api/master/internship-supervisor', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'email', 'phone', 'address', 'created_at', 'updated_at']
            ]);

        $this->assertDatabaseHas('internship_supervisors', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_an_internship_supervisor()
    {
        $response = $this->postJson('/api/master/internship-supervisor', [
            'email' => 'john.doe@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 123'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Nama wajib diisi.',
            ]);
    }

    /** @test */
    public function it_requires_email_when_creating_an_internship_supervisor()
    {
        $response = $this->postJson('/api/master/internship-supervisor', [
            'name' => 'John Doe',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 123'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Email wajib diisi.',
            ]);
    }

    /** @test */
    public function it_can_show_an_internship_supervisor()
    {
        $internshipSupervisor = InternshipSupervisor::factory()->create();

        $response = $this->getJson("/api/master/internship-supervisor/{$internshipSupervisor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data supervisor magang berhasil diambil',
                'data' => [
                    'id' => $internshipSupervisor->id,
                    'name' => $internshipSupervisor->name,
                    'email' => $internshipSupervisor->email,
                    'phone' => $internshipSupervisor->phone,
                    'address' => $internshipSupervisor->address
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_internship_supervisor()
    {
        $response = $this->getJson('/api/master/internship-supervisor/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Supervisor magang tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_an_internship_supervisor()
    {
        $internshipSupervisor = InternshipSupervisor::factory()->create();
        $updatedData = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '081234567891',
            'address' => 'Jl. Merdeka No. 124'
        ];

        $response = $this->putJson("/api/master/internship-supervisor/{$internshipSupervisor->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Supervisor magang berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('internship_supervisors', $updatedData);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_internship_supervisor()
    {
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '081234567891',
            'address' => 'Jl. Merdeka No. 124'
        ];

        $response = $this->putJson('/api/master/internship-supervisor/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Supervisor magang tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_an_internship_supervisor()
    {
        $internshipSupervisor = InternshipSupervisor::factory()->create();

        $response = $this->deleteJson("/api/master/internship-supervisor/{$internshipSupervisor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Supervisor magang berhasil dihapus'
            ]);

        // Check that the record still exists in the database but is soft deleted
        $this->assertSoftDeleted('internship_supervisors', ['id' => $internshipSupervisor->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_internship_supervisor()
    {
        $response = $this->deleteJson('/api/master/internship-supervisor/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Supervisor magang tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_internship_supervisors()
    {
        $internshipSupervisor = InternshipSupervisor::factory()->create();
        $internshipSupervisor->delete();

        $response = $this->getJson('/api/master/internship-supervisor/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data supervisor magang yang dihapus berhasil diambil',
                'data' => [
                    [
                        'id' => $internshipSupervisor->id,
                        'name' => $internshipSupervisor->name,
                        'email' => $internshipSupervisor->email,
                        'phone' => $internshipSupervisor->phone,
                        'address' => $internshipSupervisor->address,
                        'deleted_at' => $internshipSupervisor->deleted_at->toISOString()
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_restore_a_trashed_internship_supervisor()
    {
        $internshipSupervisor = InternshipSupervisor::factory()->create();
        $internshipSupervisor->delete();

        $response = $this->postJson("/api/master/internship-supervisor/{$internshipSupervisor->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Supervisor magang berhasil dipulihkan',
                'data' => [
                    'id' => $internshipSupervisor->id,
                    'name' => $internshipSupervisor->name,
                    'email' => $internshipSupervisor->email,
                    'phone' => $internshipSupervisor->phone,
                    'address' => $internshipSupervisor->address
                ]
            ]);

        // Check that the record is no longer soft deleted
        $this->assertNotSoftDeleted('internship_supervisors', ['id' => $internshipSupervisor->id]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_trashed_internship_supervisor()
    {
        $response = $this->postJson('/api/master/internship-supervisor/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Supervisor magang yang dihapus tidak ditemukan'
            ]);
    }
}
