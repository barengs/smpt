<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Program;
use App\Models\StudentResignation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentResignationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $program;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->program = Program::factory()->create();
    }

    /** @test */
    public function test_can_get_resignations_list()
    {
        $student = Student::factory()->create([
            'program_id' => $this->program->id,
            'status' => 'Aktif',
        ]);

        StudentResignation::create([
            'student_id' => $student->id,
            'submission_type' => 'biasa',
            'status' => 'pending',
            'note' => 'Some resignation note',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/main/student-resignations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'student_id',
                            'submission_type',
                            'status',
                            'note',
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function test_can_create_resignation_for_aktif_student()
    {
        $student = Student::factory()->create([
            'program_id' => $this->program->id,
            'status' => 'Aktif',
        ]);

        $file = UploadedFile::fake()->create('letter.pdf', 100);

        $payload = [
            'student_id' => $student->id,
            'submission_type' => 'biasa',
            'note' => 'Leaving due to health issues',
            'attachment' => $file,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/student-resignations', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('student_resignations', [
            'student_id' => $student->id,
            'submission_type' => 'biasa',
            'status' => 'pending',
            'note' => 'Leaving due to health issues',
        ]);
    }

    /** @test */
    public function test_cannot_create_resignation_with_invalid_student_status()
    {
        // Student status is 'Tidak Aktif', which is invalid for 'biasa' (requires 'Aktif')
        $student = Student::factory()->create([
            'program_id' => $this->program->id,
            'status' => 'Tidak Aktif',
        ]);

        $file = UploadedFile::fake()->create('letter.pdf', 100);

        $payload = [
            'student_id' => $student->id,
            'submission_type' => 'biasa',
            'note' => 'Should fail',
            'attachment' => $file,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/student-resignations', $payload);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_can_approve_resignation_and_sets_student_inactive()
    {
        $student = Student::factory()->create([
            'program_id' => $this->program->id,
            'status' => 'Aktif',
        ]);

        $resignation = StudentResignation::create([
            'student_id' => $student->id,
            'submission_type' => 'biasa',
            'status' => 'pending',
        ]);

        $payload = [
            'student_id' => $student->id,
            'submission_type' => 'biasa',
            'status' => 'disetujui',
            'note' => 'Approved resignation',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/main/student-resignations/{$resignation->id}", array_merge($payload, ['_method' => 'PUT']));

        $response->assertStatus(200);

        $this->assertDatabaseHas('student_resignations', [
            'id' => $resignation->id,
            'status' => 'disetujui',
            'processed_by' => $this->user->id,
        ]);

        // Student status should now be 'Tidak Aktif'
        $this->assertEquals('Tidak Aktif', $student->fresh()->status);
    }

    /** @test */
    public function test_can_approve_pasca_tugas_resignation_and_sets_student_lulus()
    {
        $student = Student::factory()->create([
            'program_id' => $this->program->id,
            'status' => 'Tugas',
        ]);

        $resignation = StudentResignation::create([
            'student_id' => $student->id,
            'submission_type' => 'pasca_tugas',
            'status' => 'pending',
        ]);

        $payload = [
            'student_id' => $student->id,
            'submission_type' => 'pasca_tugas',
            'status' => 'disetujui',
            'note' => 'Approved pasca tugas resignation',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/main/student-resignations/{$resignation->id}", array_merge($payload, ['_method' => 'PUT']));

        $response->assertStatus(200);

        $this->assertDatabaseHas('student_resignations', [
            'id' => $resignation->id,
            'status' => 'disetujui',
            'processed_by' => $this->user->id,
        ]);

        // Student status should now be 'Lulus'
        $this->assertEquals('Lulus', $student->fresh()->status);
    }

    /** @test */
    public function test_can_delete_pending_resignation()
    {
        $student = Student::factory()->create([
            'program_id' => $this->program->id,
            'status' => 'Aktif',
        ]);

        $resignation = StudentResignation::create([
            'student_id' => $student->id,
            'submission_type' => 'biasa',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/main/student-resignations/{$resignation->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('student_resignations', [
            'id' => $resignation->id,
        ]);
    }
}
