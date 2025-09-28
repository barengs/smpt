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
                    '*' => ['id', 'year', 'type', 'periode', 'start_date', 'end_date', 'active', 'description', 'created_at', 'updated_at']
                ]
            ]);
    }

    /** @test */
    public function it_can_create_an_academic_year()
    {
        $data = [
            'year' => '2023/2024',
            'type' => 'semester',
            'periode' => 'ganjil',
            'start_date' => '2023-07-01',
            'end_date' => '2024-06-30',
            'active' => true,
            'description' => 'Tahun ajaran 2023/2024'
        ];

        $response = $this->postJson('/api/master/academic-year', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'year', 'type', 'periode', 'start_date', 'end_date', 'active', 'description', 'created_at', 'updated_at']
            ]);

        // Check that the record exists in the database
        $this->assertDatabaseHas('academic_years', [
            'year' => '2023/2024',
            'type' => 'semester',
            'periode' => 'ganjil',
            'active' => 1,
            'description' => 'Tahun ajaran 2023/2024'
        ]);

        // Check dates separately since they include time
        $academicYear = AcademicYear::where('year', '2023/2024')->first();
        $this->assertEquals('2023-07-01', $academicYear->start_date->format('Y-m-d'));
        $this->assertEquals('2024-06-30', $academicYear->end_date->format('Y-m-d'));
    }

    /** @test */
    public function it_requires_year_when_creating_an_academic_year()
    {
        $response = $this->postJson('/api/master/academic-year', [
            'type' => 'semester',
            'periode' => 'ganjil',
            'start_date' => '2023-07-01',
            'end_date' => '2024-06-30',
            'active' => true,
            'description' => 'Tahun ajaran tanpa tahun'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Tahun ajaran wajib diisi.',
            ]);
    }

    /** @test */
    public function it_requires_type_when_creating_an_academic_year()
    {
        $response = $this->postJson('/api/master/academic-year', [
            'year' => '2023/2024',
            'periode' => 'ganjil',
            'start_date' => '2023-07-01',
            'end_date' => '2024-06-30',
            'active' => true,
            'description' => 'Tahun ajaran tanpa tipe'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Tipe tahun ajaran wajib diisi.',
            ]);
    }

    /** @test */
    public function it_requires_periode_when_creating_an_academic_year()
    {
        $response = $this->postJson('/api/master/academic-year', [
            'year' => '2023/2024',
            'type' => 'semester',
            'start_date' => '2023-07-01',
            'end_date' => '2024-06-30',
            'active' => true,
            'description' => 'Tahun ajaran tanpa periode'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Periode wajib diisi.',
            ]);
    }

    /** @test */
    public function it_requires_start_date_when_creating_an_academic_year()
    {
        $response = $this->postJson('/api/master/academic-year', [
            'year' => '2023/2024',
            'type' => 'semester',
            'periode' => 'ganjil',
            'end_date' => '2024-06-30',
            'active' => true,
            'description' => 'Tahun ajaran tanpa tanggal mulai'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Tanggal mulai wajib diisi.',
            ]);
    }

    /** @test */
    public function it_requires_end_date_when_creating_an_academic_year()
    {
        $response = $this->postJson('/api/master/academic-year', [
            'year' => '2023/2024',
            'type' => 'semester',
            'periode' => 'ganjil',
            'start_date' => '2023-07-01',
            'active' => true,
            'description' => 'Tahun ajaran tanpa tanggal selesai'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Tanggal selesai wajib diisi.',
            ]);
    }

    /** @test */
    public function it_validates_end_date_must_be_after_start_date()
    {
        $response = $this->postJson('/api/master/academic-year', [
            'year' => '2023/2024',
            'type' => 'semester',
            'periode' => 'ganjil',
            'start_date' => '2024-07-01',
            'end_date' => '2023-06-30', // End date before start date
            'active' => true,
            'description' => 'Tahun ajaran dengan tanggal tidak valid'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Tanggal selesai harus setelah tanggal mulai.',
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
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'year',
                    'type',
                    'periode',
                    'start_date',
                    'end_date',
                    'active',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at'
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
            'type' => 'triwulan',
            'periode' => 'cawu 1',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'active' => false,
            'description' => 'Tahun ajaran 2026/2027'
        ];

        $response = $this->putJson("/api/master/academic-year/{$academicYear->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tahun ajaran berhasil diperbarui'
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'year',
                    'type',
                    'periode',
                    'start_date',
                    'end_date',
                    'active',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
            ]);

        // Check that the record exists in the database
        $this->assertDatabaseHas('academic_years', [
            'year' => '2026/2027',
            'type' => 'triwulan',
            'periode' => 'cawu 1',
            'active' => 0,
            'description' => 'Tahun ajaran 2026/2027'
        ]);

        // Check dates separately since they include time
        $academicYear = AcademicYear::where('year', '2026/2027')->first();
        $this->assertEquals('2026-07-01', $academicYear->start_date->format('Y-m-d'));
        $this->assertEquals('2027-06-30', $academicYear->end_date->format('Y-m-d'));
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_academic_year()
    {
        $data = [
            'year' => '2024/2025',
            'type' => 'semester',
            'periode' => 'genap',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
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
            ])
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'year',
                        'type',
                        'periode',
                        'start_date',
                        'end_date',
                        'active',
                        'description',
                        'created_at',
                        'updated_at',
                        'deleted_at'
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
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'year',
                    'type',
                    'periode',
                    'start_date',
                    'end_date',
                    'active',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at'
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
