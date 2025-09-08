<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\Hostel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a hostel first
        $hostel = Hostel::factory()->create();

        // Create a room
        $this->room = Room::factory()->create([
            'hostel_id' => $hostel->id
        ]);
    }

    /** @test */
    public function it_can_list_all_rooms()
    {
        Room::factory()->count(3)->create();

        $response = $this->getJson('/api/master/room');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data ruangan berhasil diambil'
            ])
            ->assertJsonCount(4, 'data.data');
    }

    /** @test */
    public function it_can_create_a_room()
    {
        $hostel = Hostel::factory()->create();

        $data = [
            'name' => 'Room 101',
            'hostel_id' => $hostel->id,
            'capacity' => 4,
            'description' => 'First floor room',
            'is_active' => true
        ];

        $response = $this->postJson('/api/master/room', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Ruangan berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('rooms', $data);
    }

    /** @test */
    public function it_requires_name_when_creating_a_room()
    {
        $response = $this->postJson('/api/master/room', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_requires_valid_hostel_id_when_creating_a_room()
    {
        $data = [
            'name' => 'Room 101',
            'hostel_id' => 999999, // Non-existent hostel
            'capacity' => 4,
            'is_active' => true
        ];

        $response = $this->postJson('/api/master/room', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_show_a_room()
    {
        $response = $this->getJson("/api/master/room/{$this->room->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data ruangan berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_room()
    {
        $response = $this->getJson('/api/master/room/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_room()
    {
        $hostel = Hostel::factory()->create();

        $data = [
            'name' => 'Updated Room 101',
            'hostel_id' => $hostel->id,
            'capacity' => 6,
            'description' => 'Updated first floor room',
            'is_active' => false
        ];

        $response = $this->putJson("/api/master/room/{$this->room->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ruangan berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('rooms', $data);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_room()
    {
        $hostel = Hostel::factory()->create();

        $data = [
            'name' => 'Room 202',
            'hostel_id' => $hostel->id,
            'capacity' => 4,
            'is_active' => true
        ];

        $response = $this->putJson('/api/master/room/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_room()
    {
        $response = $this->deleteJson("/api/master/room/{$this->room->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ruangan berhasil dihapus'
            ]);

        $this->assertSoftDeleted('rooms', ['id' => $this->room->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_room()
    {
        $response = $this->deleteJson('/api/master/room/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_room()
    {
        $this->room->delete();

        $response = $this->postJson("/api/master/room/{$this->room->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ruangan berhasil dipulihkan'
            ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $this->room->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_room()
    {
        $response = $this->postJson('/api/master/room/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_room()
    {
        // Mencoba memulihkan ruangan yang tidak dihapus
        $response = $this->postJson("/api/master/room/{$this->room->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Ruangan tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_rooms()
    {
        $this->room->delete();

        $response = $this->getJson('/api/master/room/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data ruangan terhapus berhasil diambil'
            ]);
    }
}
