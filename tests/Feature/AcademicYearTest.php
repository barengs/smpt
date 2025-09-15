<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_academic_years()
    {
        // Create academic years with unique years
        AcademicYear::factory()->create(['year' => '2020/2021']);
        AcademicYear::factory()->create(['year' => '2021/2022']);
        AcademicYear::factory()->create(['year' => '2022/2023']);

        $response = $this->getJson('/api/master/academic-year');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'year', 'active', 'description', 'created_at', 'updated_at']
                ]
            ]);
    }

    /** @test */
    public function it_can_create_an_academic_year()
    {
        $data = [
            'year' => '2023/2024',
            'active' => true,
            'description' => 'Tahun ajaran 2023/2024'
        ];

        $response = $this->postJson('/api/master/academic-year', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'year', 'active', 'description', 'created_at', 'updated_at']
            ]);

        $this->assertDatabaseHas('academic_years', $data);
    }

    /** @test */
    public function it_requires_year_when_creating_an_academic_year()
    {
        $response = $this->postJson('/api/master/academic-year', [
            'active' => true,
            'description' => 'Tahun ajaran tanpa tahun'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Tahun ajaran wajib diisi.',
            ]);
    }

    /** @test */
    public function it_can_show_an_academic_year()
    {
        $academicYear = AcademicYear::factory()->create(['year' => '2024/2025']);

        $response = $this->getJson("/api/master/academic-year/{$academicYear->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data tahun ajaran berhasil diambil',
                'data' => [
                    'id' => $academicYear->id,
                    'year' => $academicYear->year,
                    'active' => $academicYear->active,
                    'description' => $academicYear->description
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_academic_year()
    {
        $response = $this->getJson('/api/master/academic-year/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Tahun ajaran tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_an_academic_year()
    {
        $academicYear = AcademicYear::factory()->create(['year' => '2025/2026']);
        $updatedData = [
            'year' => '2026/2027',
            'active' => false,
            'description' => 'Tahun ajaran 2026/2027'
        ];

        $response = $this->putJson("/api/master/academic-year/{$academicYear->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tahun ajaran berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('academic_years', $updatedData);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_academic_year()
    {
        $data = [
            'year' => '2024/2025',
            'active' => false,
            'description' => 'Tahun ajaran 2024/2025'
        ];

        $response = $this->putJson('/api/master/academic-year/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Tahun ajaran tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_an_academic_year()
    {
        $academicYear = AcademicYear::factory()->create(['year' => '2027/2028']);

        $response = $this->deleteJson("/api/master/academic-year/{$academicYear->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tahun ajaran berhasil dihapus'
            ]);

        // Check that the record still exists in the database but is soft deleted
        $this->assertSoftDeleted('academic_years', ['id' => $academicYear->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_academic_year()
    {
        $response = $this->deleteJson('/api/master/academic-year/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Tahun ajaran tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_list_trashed_academic_years()
    {
        $academicYear = AcademicYear::factory()->create(['year' => '2028/2029']);
        $academicYear->delete(); // Soft delete

        $response = $this->getJson('/api/master/academic-year/trashed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data tahun ajaran yang dihapus berhasil diambil',
                'data' => [
                    [
                        'id' => $academicYear->id,
                        'year' => $academicYear->year,
                        'active' => $academicYear->active,
                        'description' => $academicYear->description,
                        'deleted_at' => $academicYear->deleted_at->toISOString()
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_restore_a_trashed_academic_year()
    {
        $academicYear = AcademicYear::factory()->create(['year' => '2029/2030']);
        $academicYear->delete(); // Soft delete

        $response = $this->postJson("/api/master/academic-year/{$academicYear->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tahun ajaran berhasil dipulihkan',
                'data' => [
                    'id' => $academicYear->id,
                    'year' => $academicYear->year,
                    'active' => $academicYear->active,
                    'description' => $academicYear->description
                ]
            ]);

        // Check that the record is no longer soft deleted
        $this->assertNotSoftDeleted('academic_years', ['id' => $academicYear->id]);
    }

    /** @test */
    public function it_returns_404_when_restoring_nonexistent_trashed_academic_year()
    {
        $response = $this->postJson('/api/master/academic-year/999999/restore');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Tahun ajaran yang dihapus tidak ditemukan'
            ]);
    }
}
