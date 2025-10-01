<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\AcademicYear;
use App\Models\EducationalInstitution;
use App\Models\Classroom;
use App\Models\ClassGroup;
use App\Models\LessonHour;
use App\Models\Staff;
use App\Models\Study;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClassScheduleWithStudentsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $academicYear;
    protected $education;
    protected $classroom;
    protected $classGroup;
    protected $lessonHour;
    protected $teacher;
    protected $study;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with admin role
        $this->user = User::factory()->create();

        // Create related data
        $this->academicYear = AcademicYear::factory()->create();
        $this->education = EducationalInstitution::factory()->create();
        $this->classroom = Classroom::factory()->create();
        $this->classGroup = ClassGroup::factory()->create([
            'classroom_id' => $this->classroom->id
        ]);
        $this->lessonHour = LessonHour::factory()->create();
        $this->teacher = Staff::factory()->create();
        $this->study = Study::factory()->create();
        $this->student = Student::factory()->create();

        // Create a student class mapping
        StudentClass::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'educational_institution_id' => $this->education->id,
            'classroom_id' => $this->classroom->id,
            'class_group_id' => $this->classGroup->id,
            'student_id' => $this->student->id,
            'approval_status' => 'disetujui'
        ]);
    }

    /** @test */
    public function it_includes_student_data_in_class_schedule_response()
    {
        // Create a class schedule
        $schedule = ClassSchedule::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'educational_institution_id' => $this->education->id,
        ]);

        // Create a detail for this schedule
        $detail = \App\Models\ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'classroom_id' => $this->classroom->id,
            'class_group_id' => $this->classGroup->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/main/class-schedule');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data jadwal berhasil diambil',
                'status' => 200
            ]);

        // Check that the response contains student data
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'details' => [
                        '*' => [
                            'students'
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_includes_student_data_in_single_class_schedule_response()
    {
        // Create a class schedule
        $schedule = ClassSchedule::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'educational_institution_id' => $this->education->id,
        ]);

        // Create a detail for this schedule
        $detail = \App\Models\ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'classroom_id' => $this->classroom->id,
            'class_group_id' => $this->classGroup->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/main/class-schedule/{$schedule->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data jadwal berhasil diambil',
                'status' => 200
            ]);

        // Check that the response contains student data
        $response->assertJsonStructure([
            'data' => [
                'details' => [
                    '*' => [
                        'students'
                    ]
                ]
            ]
        ]);
    }
}
