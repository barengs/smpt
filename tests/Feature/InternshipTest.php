<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Internship;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\InternshipSupervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InternshipTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $academicYear;
    protected $student;
    protected $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with admin role
        $this->user = User::factory()->create();

        // Create related data
        $this->academicYear = AcademicYear::factory()->create();
        $this->student = Student::factory()->create();
        $this->supervisor = InternshipSupervisor::factory()->create();
    }

    /** @test */
    public function it_can_create_an_internship()
    {
        $data = [
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'supervisor_id' => $this->supervisor->id,
            'status' => 'pending',
            'file' => 'internship.pdf',
            'long_term' => 3,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/internship', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data magang berhasil disimpan',
            ]);

        $this->assertDatabaseHas('internships', [
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'supervisor_id' => $this->supervisor->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_can_get_all_internships()
    {
        $internship = Internship::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/main/internship');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data magang berhasil diambil',
            ]);
    }

    /** @test */
    public function it_can_get_a_specific_internship()
    {
        $internship = Internship::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/main/internship/{$internship->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data magang berhasil diambil',
            ]);
    }

    /** @test */
    public function it_can_update_an_internship()
    {
        $internship = Internship::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'supervisor_id' => $this->supervisor->id,
            'status' => 'pending',
        ]);

        $data = [
            'status' => 'approved',
            'file' => 'updated_internship.pdf',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/main/internship/{$internship->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data magang berhasil diperbarui',
            ]);

        $this->assertDatabaseHas('internships', [
            'id' => $internship->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function it_can_delete_an_internship()
    {
        $internship = Internship::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/main/internship/{$internship->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data magang berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('internships', [
            'id' => $internship->id,
        ]);
    }

    /** @test */
    public function it_requires_validation_for_creating_internship()
    {
        $data = []; // Empty data

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/internship', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal',
            ]);
    }
}
