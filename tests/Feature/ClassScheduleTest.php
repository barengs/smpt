<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\AcademicYear;
use App\Models\Education;
use App\Models\Classroom;
use App\Models\ClassGroup;
use App\Models\LessonHour;
use App\Models\Staff;
use App\Models\Study;
use App\Models\MeetingSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClassScheduleTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with admin role
        $this->user = User::factory()->create();

        // Create related data
        $this->academicYear = AcademicYear::factory()->create();
        $this->education = Education::factory()->create();
        $this->classroom = Classroom::factory()->create();
        $this->classGroup = ClassGroup::factory()->create([
            'classroom_id' => $this->classroom->id
        ]);
        $this->lessonHour = LessonHour::factory()->create();
        $this->teacher = Staff::factory()->create();
        $this->study = Study::factory()->create();
    }

    /** @test */
    public function it_can_create_a_class_schedule()
    {
        $data = [
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
            'session' => 'pagi',
            'status' => 'active',
            'details' => [
                [
                    'classroom_id' => $this->classroom->id,
                    'class_group_id' => $this->classGroup->id,
                    'day' => 'senin',
                    'lesson_hour_id' => $this->lessonHour->id,
                    'teacher_id' => $this->teacher->id,
                    'study_id' => $this->study->id,
                ]
            ]
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/class-schedule', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Jadwal berhasil disimpan',
                'status' => 201
            ]);

        $this->assertDatabaseHas('class_schedules', [
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
            'session' => 'pagi',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('class_schedule_details', [
            'classroom_id' => $this->classroom->id,
            'class_group_id' => $this->classGroup->id,
            'day' => 'senin',
            'lesson_hour_id' => $this->lessonHour->id,
            'teacher_id' => $this->teacher->id,
            'study_id' => $this->study->id,
        ]);
    }

    /** @test */
    public function it_can_create_a_class_schedule_with_meeting_schedules()
    {
        $data = [
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
            'session' => 'pagi',
            'status' => 'active',
            'details' => [
                [
                    'classroom_id' => $this->classroom->id,
                    'class_group_id' => $this->classGroup->id,
                    'day' => 'senin',
                    'lesson_hour_id' => $this->lessonHour->id,
                    'teacher_id' => $this->teacher->id,
                    'study_id' => $this->study->id,
                    'meeting_count' => 5
                ]
            ]
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/class-schedule', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Jadwal berhasil disimpan',
                'status' => 201
            ]);

        // Check that the class schedule was created
        $this->assertDatabaseHas('class_schedules', [
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
            'session' => 'pagi',
            'status' => 'active',
        ]);

        // Check that the class schedule detail was created
        $this->assertDatabaseHas('class_schedule_details', [
            'classroom_id' => $this->classroom->id,
            'class_group_id' => $this->classGroup->id,
            'day' => 'senin',
            'lesson_hour_id' => $this->lessonHour->id,
            'teacher_id' => $this->teacher->id,
            'study_id' => $this->study->id,
        ]);

        // Check that meeting schedules were created
        $detail = \App\Models\ClassScheduleDetail::first();
        $this->assertDatabaseCount('meeting_schedules', 5);
        $this->assertDatabaseHas('meeting_schedules', [
            'class_schedule_detail_id' => $detail->id,
            'meeting_sequence' => 1
        ]);
    }

    /** @test */
    public function it_can_get_all_class_schedules()
    {
        $schedule = ClassSchedule::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
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
    }

    /** @test */
    public function it_can_get_a_specific_class_schedule()
    {
        $schedule = ClassSchedule::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
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
    }

    /** @test */
    public function it_can_update_a_class_schedule()
    {
        $schedule = ClassSchedule::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
            'session' => 'pagi',
        ]);

        $newClassGroup = ClassGroup::factory()->create([
            'classroom_id' => $this->classroom->id
        ]);

        $data = [
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
            'session' => 'sore',
            'status' => 'active',
            'details' => [
                [
                    'classroom_id' => $this->classroom->id,
                    'class_group_id' => $newClassGroup->id,
                    'day' => 'selasa',
                    'lesson_hour_id' => $this->lessonHour->id,
                    'teacher_id' => $this->teacher->id,
                    'study_id' => $this->study->id,
                    'meeting_count' => 3
                ]
            ]
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/main/class-schedule/{$schedule->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Jadwal berhasil diperbarui',
                'status' => 200
            ]);

        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'session' => 'sore',
        ]);

        $this->assertDatabaseHas('class_schedule_details', [
            'class_schedule_id' => $schedule->id,
            'class_group_id' => $newClassGroup->id,
            'day' => 'selasa',
        ]);

        // Check that meeting schedules were created
        $detail = \App\Models\ClassScheduleDetail::first();
        $this->assertDatabaseCount('meeting_schedules', 3);
        $this->assertDatabaseHas('meeting_schedules', [
            'class_schedule_detail_id' => $detail->id,
            'meeting_sequence' => 1
        ]);
    }

    /** @test */
    public function it_can_delete_a_class_schedule()
    {
        $schedule = ClassSchedule::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'education_id' => $this->education->id,
        ]);

        // Create a detail for this schedule
        $detail = \App\Models\ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'classroom_id' => $this->classroom->id,
            'class_group_id' => $this->classGroup->id,
            'lesson_hour_id' => $this->lessonHour->id,
            'teacher_id' => $this->teacher->id,
            'study_id' => $this->study->id,
        ]);

        // Create meeting schedules for this detail
        MeetingSchedule::factory()->count(3)->create([
            'class_schedule_detail_id' => $detail->id
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/main/class-schedule/{$schedule->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Jadwal berhasil dihapus',
                'status' => 200
            ]);

        $this->assertDatabaseMissing('class_schedules', [
            'id' => $schedule->id,
        ]);

        $this->assertDatabaseMissing('class_schedule_details', [
            'id' => $detail->id,
        ]);

        // Check that meeting schedules were also deleted
        $this->assertDatabaseCount('meeting_schedules', 0);
    }

    /** @test */
    public function it_requires_validation_for_creating_schedule()
    {
        $data = []; // Empty data

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/class-schedule', $data);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validasi gagal',
                'status' => 422
            ]);
    }
}
