<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Presence;
use App\Models\Student;
use App\Models\MeetingSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\ClassSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PresenceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $student;
    protected $meetingSchedule;
    protected $classScheduleDetail;
    protected $classSchedule;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with admin role
        $this->user = User::factory()->create();

        // Create related data
        $this->student = Student::factory()->create();
        $this->classSchedule = ClassSchedule::factory()->create();
        $this->classScheduleDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $this->classSchedule->id
        ]);
        $this->meetingSchedule = MeetingSchedule::factory()->create([
            'class_schedule_detail_id' => $this->classScheduleDetail->id
        ]);
    }

    /** @test */
    public function it_can_create_a_presence()
    {
        $data = [
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'status' => 'hadir',
            'description' => 'Hadir tepat waktu',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/presence', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data presensi berhasil disimpan',
            ]);

        $this->assertDatabaseHas('presences', [
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'status' => 'hadir',
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
        ]);
    }

    /** @test */
    public function it_can_get_all_presences()
    {
        $presence = Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/main/presence');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data presensi berhasil diambil',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'student_id',
                        'meeting_schedule_id',
                        'status',
                        'description',
                        'date',
                        'user_id',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_presence_by_class_schedule()
    {
        // Create student class mapping
        \App\Models\StudentClass::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->classSchedule->academic_year_id,
            'educational_institution_id' => $this->classSchedule->educational_institution_id,
            'classroom_id' => $this->classScheduleDetail->classroom_id,
            'class_group_id' => $this->classScheduleDetail->class_group_id,
            'approval_status' => 'disetujui'
        ]);

        // Create presence
        $presence = Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'user_id' => $this->user->id,
            'status' => 'hadir'
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/main/presence?class_schedule_id={$this->classSchedule->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data presensi berdasarkan jadwal kelas berhasil diambil',
            ])
            ->assertJsonStructure([
                'data' => [
                    'details' => [
                        '*' => [
                            'students',
                            'meeting_schedules' => [
                                '*' => [
                                    'presences'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_a_specific_presence()
    {
        $presence = Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/main/presence/{$presence->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data presensi berhasil diambil',
            ]);
    }

    /** @test */
    public function it_can_update_a_presence()
    {
        $presence = Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'status' => 'alpha',
            'user_id' => $this->user->id,
        ]);

        $data = [
            'status' => 'izin',
            'description' => 'Izin karena sakit',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/main/presence/{$presence->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data presensi berhasil diperbarui',
            ]);

        $this->assertDatabaseHas('presences', [
            'id' => $presence->id,
            'status' => 'izin',
        ]);
    }

    /** @test */
    public function it_can_delete_a_presence()
    {
        $presence = Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/main/presence/{$presence->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data presensi berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('presences', [
            'id' => $presence->id,
        ]);
    }

    /** @test */
    public function it_can_get_presence_statistics()
    {
        // Create multiple presences with different statuses
        Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'status' => 'hadir',
            'user_id' => $this->user->id,
        ]);

        Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'status' => 'izin',
            'user_id' => $this->user->id,
        ]);

        Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'status' => 'sakit',
            'user_id' => $this->user->id,
        ]);

        Presence::factory()->create([
            'student_id' => $this->student->id,
            'meeting_schedule_id' => $this->meetingSchedule->id,
            'status' => 'alpha',
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/main/presence/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Statistik presensi berhasil diambil',
            ])
            ->assertJsonStructure([
                'data' => [
                    'hadir',
                    'izin',
                    'sakit',
                    'alpha',
                    'total',
                    'percentages' => [
                        'hadir',
                        'izin',
                        'sakit',
                        'alpha',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_requires_validation_for_creating_presence()
    {
        $data = []; // Empty data

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/main/presence', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal',
            ]);
    }
}
