<?php

namespace Tests\Feature;

use App\Models\Education;
use App\Models\EducationClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationTest extends TestCase
{
    use RefreshDatabase;

    protected $education;

    protected function setUp(): void
    {
        parent::setUp();
        $this->education = Education::factory()->create();
    }

    /** @test */
    public function it_can_list_all_education()
    {
        Education::factory()->count(3)->create();

        $response = $this->getJson('/api/master/education');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pendidikan berhasil diambil'
            ])
            ->assertJsonCount(4, 'data.data');
    }

    /** @test */
    public function it_can_create_a_education_with_multiple_classes()
    {
        // Create some education classes
        $educationClasses = EducationClass::factory()->count(3)->create();
        $educationClassIds = $educationClasses->pluck('id')->toArray();

        $data = [
            'name' => 'Sekolah Dasar',
            'description' => 'Jenjang pendidikan dasar',
            'education_class_ids' => $educationClassIds
        ];

        $response = $this->postJson('/api/master/education', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil ditambahkan'
            ])
            ->assertJsonStructure([
                'data' => [
                    'education_class'
                ]
            ]);

        // Check that the education was created
        $this->assertDatabaseHas('educations', [
            'name' => 'Sekolah Dasar',
            'description' => 'Jenjang pendidikan dasar'
        ]);

        // Check that the relationships were created
        foreach ($educationClassIds as $classId) {
            $this->assertDatabaseHas('education_has_education_classes', [
                'education_id' => $response->json('data.id'),
                'education_class_id' => $classId
            ]);
        }
    }

    /** @test */
    public function it_requires_education_class_ids_when_creating_a_education()
    {
        $data = [
            'name' => 'Sekolah Dasar',
            'description' => 'Jenjang pendidikan dasar'
            // Missing education_class_ids
        ];

        $response = $this->postJson('/api/master/education', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_requires_valid_education_class_ids_when_creating_a_education()
    {
        $data = [
            'name' => 'Sekolah Dasar',
            'description' => 'Jenjang pendidikan dasar',
            'education_class_ids' => [999999] // Non-existent ID
        ];

        $response = $this->postJson('/api/master/education', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_show_a_education()
    {
        $response = $this->getJson("/api/master/education/{$this->education->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pendidikan berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_education()
    {
        $response = $this->getJson('/api/master/education/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_education_with_multiple_classes()
    {
        // Create some education classes
        $educationClasses = EducationClass::factory()->count(2)->create();
        $educationClassIds = $educationClasses->pluck('id')->toArray();

        $data = [
            'name' => 'Sekolah Menengah Pertama',
            'description' => 'Jenjang pendidikan menengah pertama',
            'education_class_ids' => $educationClassIds
        ];

        $response = $this->putJson("/api/master/education/{$this->education->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil diperbarui'
            ])
            ->assertJsonStructure([
                'data' => [
                    'education_class'
                ]
            ]);

        // Check that the education was updated
        $this->assertDatabaseHas('educations', [
            'id' => $this->education->id,
            'name' => 'Sekolah Menengah Pertama',
            'description' => 'Jenjang pendidikan menengah pertama'
        ]);

        // Check that the relationships were updated
        foreach ($educationClassIds as $classId) {
            $this->assertDatabaseHas('education_has_education_classes', [
                'education_id' => $this->education->id,
                'education_class_id' => $classId
            ]);
        }
    }

    /** @test */
    public function it_can_update_a_education_without_changing_classes()
    {
        $data = [
            'name' => 'Sekolah Menengah Pertama',
            'description' => 'Jenjang pendidikan menengah pertama'
            // No education_class_ids provided
        ];

        $response = $this->putJson("/api/master/education/{$this->education->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil diperbarui'
            ]);

        // Check that the education was updated
        $this->assertDatabaseHas('educations', [
            'id' => $this->education->id,
            'name' => 'Sekolah Menengah Pertama',
            'description' => 'Jenjang pendidikan menengah pertama'
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_education()
    {
        $data = [
            'name' => 'Sekolah Menengah Atas',
            'description' => 'Jenjang pendidikan menengah atas'
        ];

        $response = $this->putJson('/api/master/education/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_education()
    {
        $response = $this->deleteJson("/api/master/education/{$this->education->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil dihapus'
            ]);

        $this->assertSoftDeleted('educations', ['id' => $this->education->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_education()
    {
        $response = $this->deleteJson('/api/master/education/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_education()
    {
        $this->education->delete();

        $response = $this->postJson("/api/master/education/{$this->education->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pendidikan berhasil dipulihkan'
            ]);

        $this->assertDatabaseHas('educations', [
            'id' => $this->education->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_education()
    {
        $response = $this->postJson('/api/master/education/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_returns_400_when_restoring_non_deleted_education()
    {
        // Mencoba memulihkan pendidikan yang tidak dihapus
        $response = $this->postJson("/api/master/education/{$this->education->id}/restore");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Pendidikan tidak dalam keadaan terhapus'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_education()
    {
        $this->education->delete();

        $response = $this->getJson('/api/master/education/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pendidikan terhapus berhasil diambil'
            ]);
    }
}
