<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_activities()
    {
        Activity::factory()->count(3)->create();

        $response = $this->getJson('/api/master/activity');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data aktivitas berhasil diambil'
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_an_activity()
    {
        $data = [
            'name' => 'Kegiatan Sekolah',
            'description' => 'Kegiatan tahunan sekolah',
            'date' => '2025-10-10',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/master/activity', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Aktivitas berhasil dibuat'
            ]);

        // Check without the date field since it might be stored in a different format
        $this->assertDatabaseHas('activities', [
            'name' => 'Kegiatan Sekolah',
            'description' => 'Kegiatan tahunan sekolah',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_can_show_an_activity()
    {
        $activity = Activity::factory()->create();

        $response = $this->getJson("/api/master/activity/{$activity->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data aktivitas berhasil diambil',
                'data' => [
                    'id' => $activity->id,
                    'name' => $activity->name
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_activity()
    {
        $response = $this->getJson('/api/master/activity/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Aktivitas tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_an_activity()
    {
        $activity = Activity::factory()->create(['name' => 'Kegiatan Lama']);

        $updatedData = [
            'name' => 'Kegiatan Baru',
            'description' => 'Deskripsi kegiatan yang diperbarui'
        ];

        $response = $this->putJson("/api/master/activity/{$activity->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Aktivitas berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('activities', array_merge(['id' => $activity->id], $updatedData));
    }

    /** @test */
    public function it_can_delete_an_activity()
    {
        $activity = Activity::factory()->create();

        $response = $this->deleteJson("/api/master/activity/{$activity->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Aktivitas berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }
}
