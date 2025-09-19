<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\Education;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentClassTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_student_classes()
    {
        StudentClass::factory()->count(5)->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/main/student-class');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'academic_year_id',
                            'education_id',
                            'student_id',
                            'class_id',
                            'approval_status',
                            'approval_note',
                            'approved_by',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_student_class()
    {
        $academicYear = AcademicYear::factory()->create();
        $education = Education::factory()->create();
        $student = Student::factory()->create();
        $classroom = Classroom::factory()->create();

        $data = [
            'academic_year_id' => $academicYear->id,
            'education_id' => $education->id,
            'student_id' => $student->id,
            'class_id' => $classroom->id,
            'approval_status' => 'diajukan',
            'approval_note' => 'Test note',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/student-class', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => [
                    'id',
                    'academic_year_id',
                    'education_id',
                    'student_id',
                    'class_id',
                    'approval_status',
                    'approval_note',
                    'approved_by',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('student_classes', $data);
    }

    /** @test */
    public function it_can_show_a_student_class()
    {
        $studentClass = StudentClass::factory()->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/main/student-class/{$studentClass->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => [
                    'id',
                    'academic_year_id',
                    'education_id',
                    'student_id',
                    'class_id',
                    'approval_status',
                    'approval_note',
                    'approved_by',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function it_can_update_a_student_class()
    {
        $studentClass = StudentClass::factory()->create();
        $newClassroom = Classroom::factory()->create();

        $data = [
            'class_id' => $newClassroom->id,
            'approval_status' => 'disetujui',
            'approval_note' => 'Approved note',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/main/student-class/{$studentClass->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => [
                    'id',
                    'academic_year_id',
                    'education_id',
                    'student_id',
                    'class_id',
                    'approval_status',
                    'approval_note',
                    'approved_by',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('student_classes', [
            'id' => $studentClass->id,
            'class_id' => $newClassroom->id,
            'approval_status' => 'disetujui'
        ]);
    }

    /** @test */
    public function it_can_delete_a_student_class()
    {
        $studentClass = StudentClass::factory()->create();

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/main/student-class/{$studentClass->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data kelas siswa berhasil dihapus',
                'status' => 200
            ]);

        $this->assertDatabaseMissing('student_classes', ['id' => $studentClass->id]);
    }

    /** @test */
    public function it_requires_valid_data_to_create_a_student_class()
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/student-class', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }
}
