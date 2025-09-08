<?php

namespace Tests\Feature;

use App\Models\ClassGroup;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassGroupTest extends TestCase
{
    use RefreshDatabase;

    protected $classGroup;
    protected $classroom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classroom = Classroom::factory()->create();
        $this->classGroup = ClassGroup::factory()->create([
            'classroom_id' => $this->classroom->id
        ]);
    }

    /** @test */
    public function it_can_list_class_groups()
    {
        $response = $this->getJson('/api/master/class-group');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data kelompok kelas berhasil diambil'
            ]);
    }

    /** @test */
    public function it_can_create_a_class_group()
    {
        $classroom = Classroom::factory()->create();

        $data = [
            'name' => 'Kelompok Kelas A',
            'classroom_id' => $classroom->id
        ];

        $response = $this->postJson('/api/master/class-group', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Kelompok kelas berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('class_groups', $data);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_class_group()
    {
        $response = $this->postJson('/api/master/class-group', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_show_a_class_group()
    {
        $response = $this->getJson("/api/master/class-group/{$this->classGroup->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data kelompok kelas berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_class_group()
    {
        $response = $this->getJson('/api/master/class-group/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Kelompok kelas tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_class_group()
    {
        $classroom = Classroom::factory()->create();

        $data = [
            'name' => 'Kelompok Kelas B',
            'classroom_id' => $classroom->id
        ];

        $response = $this->putJson("/api/master/class-group/{$this->classGroup->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kelompok kelas berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('class_groups', $data);
    }

    /** @test */
    public function it_validates_required_fields_when_updating_class_group()
    {
        $response = $this->putJson("/api/master/class-group/{$this->classGroup->id}", []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_class_group()
    {
        $data = [
            'name' => 'Kelompok Kelas C',
            'classroom_id' => $this->classroom->id
        ];

        $response = $this->putJson('/api/master/class-group/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Kelompok kelas tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_class_group()
    {
        $response = $this->deleteJson("/api/master/class-group/{$this->classGroup->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kelompok kelas berhasil dihapus'
            ]);

        $this->assertSoftDeleted('class_groups', ['id' => $this->classGroup->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_class_group()
    {
        $response = $this->deleteJson('/api/master/class-group/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Kelompok kelas tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_class_group()
    {
        $this->classGroup->delete();

        $response = $this->postJson("/api/master/class-group/{$this->classGroup->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kelompok kelas berhasil dipulihkan'
            ]);

        $this->assertNotSoftDeleted('class_groups', ['id' => $this->classGroup->id]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_class_group()
    {
        $response = $this->postJson('/api/master/class-group/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Kelompok kelas tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_class_groups()
    {
        $this->classGroup->delete();

        $response = $this->getJson('/api/master/class-group/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data kelompok kelas terhapus berhasil diambil'
            ]);
    }
}
