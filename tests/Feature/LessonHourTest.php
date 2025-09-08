<?php

namespace Tests\Feature;

use App\Models\LessonHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonHourTest extends TestCase
{
    use RefreshDatabase;

    protected $lessonHour;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lessonHour = LessonHour::factory()->create();
    }

    /** @test */
    public function it_can_list_all_lesson_hours()
    {
        LessonHour::factory()->count(3)->create();

        $response = $this->getJson('/api/master/lesson-hour');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data jam pelajaran berhasil diambil'
            ])
            ->assertJsonCount(4, 'data.data');
    }

    /** @test */
    public function it_can_create_a_lesson_hour()
    {
        $data = [
            'name' => 'Period 1',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'order' => 1,
            'description' => 'First period of the day'
        ];

        $response = $this->postJson('/api/master/lesson-hour', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Jam pelajaran berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('lesson_hours', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_a_lesson_hour()
    {
        $response = $this->postJson('/api/master/lesson-hour', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_requires_valid_time_format_when_creating_a_lesson_hour()
    {
        $data = [
            'name' => 'Period 1',
            'start_time' => 'invalid-time',
            'end_time' => '09:00',
            'order' => 1
        ];

        $response = $this->postJson('/api/master/lesson-hour', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_requires_end_time_to_be_after_start_time_when_creating_a_lesson_hour()
    {
        $data = [
            'name' => 'Period 1',
            'start_time' => '09:00',
            'end_time' => '08:00', // Earlier than start time
            'order' => 1
        ];

        $response = $this->postJson('/api/master/lesson-hour', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_show_a_lesson_hour()
    {
        $response = $this->getJson("/api/master/lesson-hour/{$this->lessonHour->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data jam pelajaran berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_lesson_hour()
    {
        $response = $this->getJson('/api/master/lesson-hour/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Jam pelajaran tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_lesson_hour()
    {
        $data = [
            'name' => 'Updated Period 1',
            'start_time' => '08:30',
            'end_time' => '09:30',
            'order' => 2,
            'description' => 'Updated first period'
        ];

        $response = $this->putJson("/api/master/lesson-hour/{$this->lessonHour->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Jam pelajaran berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('lesson_hours', $data);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_lesson_hour()
    {
        $data = [
            'name' => 'Period 2',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'order' => 2
        ];

        $response = $this->putJson('/api/master/lesson-hour/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Jam pelajaran tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_lesson_hour()
    {
        $response = $this->deleteJson("/api/master/lesson-hour/{$this->lessonHour->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Jam pelajaran berhasil dihapus'
            ]);

        $this->assertSoftDeleted('lesson_hours', ['id' => $this->lessonHour->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_lesson_hour()
    {
        $response = $this->deleteJson('/api/master/lesson-hour/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Jam pelajaran tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_lesson_hour()
    {
        $this->lessonHour->delete();

        $response = $this->postJson("/api/master/lesson-hour/{$this->lessonHour->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Jam pelajaran berhasil dipulihkan'
            ]);

        $this->assertDatabaseHas('lesson_hours', [
            'id' => $this->lessonHour->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_lesson_hour()
    {
        $response = $this->postJson('/api/master/lesson-hour/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Jam pelajaran tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_lesson_hour()
    {
        // Mencoba memulihkan jam pelajaran yang tidak dihapus
        $response = $this->postJson("/api/master/lesson-hour/{$this->lessonHour->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Jam pelajaran tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_lesson_hours()
    {
        $this->lessonHour->delete();

        $response = $this->getJson('/api/master/lesson-hour/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data jam pelajaran terhapus berhasil diambil'
            ]);
    }
}
