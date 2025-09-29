<?php

namespace Tests\Feature;

use App\Models\EducationalInstitution;
use App\Models\Education;
use App\Models\EducationClass;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationalInstitutionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_educational_institutions()
    {
        // Create educational institutions
        EducationalInstitution::factory()->count(3)->create();

        $response = $this->getJson('/api/main/educational-institution');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'education_id',
                        'education_class_id',
                        'registration_number',
                        'institution_name',
                        'institution_address',
                        'institution_phone',
                        'institution_email',
                        'institution_website',
                        'institution_logo',
                        'institution_banner',
                        'institution_status',
                        'institution_description',
                        'headmaster_id',
                        'created_at',
                        'updated_at',
                        'education',
                        'educationClass',
                        'headmaster'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_create_an_educational_institution()
    {
        $education = Education::factory()->create();
        $educationClass = EducationClass::factory()->create();
        $headmaster = Staff::factory()->create();

        $data = [
            'education_id' => $education->id,
            'education_class_id' => $educationClass->id,
            'registration_number' => 'REG-12345',
            'institution_name' => 'SMA Negeri 1 Jakarta',
            'institution_address' => 'Jl. Merdeka No. 1, Jakarta',
            'institution_phone' => '021-1234567',
            'institution_email' => 'sman1@jakarta.sch.id',
            'institution_website' => 'https://sman1.jakarta.sch.id',
            'institution_logo' => 'logo.png',
            'institution_banner' => 'banner.png',
            'institution_status' => 'active',
            'institution_description' => 'Sekolah Menengah Atas Negeri 1 Jakarta',
            'headmaster_id' => $headmaster->id,
        ];

        $response = $this->postJson('/api/main/educational-institution', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Institusi pendidikan berhasil ditambahkan'
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'education_id',
                    'education_class_id',
                    'registration_number',
                    'institution_name',
                    'institution_address',
                    'institution_phone',
                    'institution_email',
                    'institution_website',
                    'institution_logo',
                    'institution_banner',
                    'institution_status',
                    'institution_description',
                    'headmaster_id',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('educational_institutions', [
            'institution_name' => 'SMA Negeri 1 Jakarta',
            'registration_number' => 'REG-12345'
        ]);
    }

    /** @test */
    public function it_requires_valid_education_id_when_creating_an_educational_institution()
    {
        $educationClass = EducationClass::factory()->create();
        $headmaster = Staff::factory()->create();

        $data = [
            'education_id' => 999999, // Invalid ID
            'education_class_id' => $educationClass->id,
            'institution_name' => 'Test Institution',
            'institution_description' => 'Test Description',
            'headmaster_id' => $headmaster->id,
        ];

        $response = $this->postJson('/api/main/educational-institution', $data);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'ID pendidikan tidak valid.',
            ]);
    }

    /** @test */
    public function it_can_show_an_educational_institution()
    {
        $educationalInstitution = EducationalInstitution::factory()->create();

        $response = $this->getJson("/api/main/educational-institution/{$educationalInstitution->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data institusi pendidikan berhasil diambil'
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'education_id',
                    'education_class_id',
                    'registration_number',
                    'institution_name',
                    'institution_address',
                    'institution_phone',
                    'institution_email',
                    'institution_website',
                    'institution_logo',
                    'institution_banner',
                    'institution_status',
                    'institution_description',
                    'headmaster_id',
                    'created_at',
                    'updated_at',
                    'education',
                    'educationClass',
                    'headmaster'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_educational_institution()
    {
        $response = $this->getJson('/api/main/educational-institution/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Institusi pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_an_educational_institution()
    {
        $educationalInstitution = EducationalInstitution::factory()->create();
        $education = Education::factory()->create();
        $educationClass = EducationClass::factory()->create();
        $headmaster = Staff::factory()->create();

        $updatedData = [
            'education_id' => $education->id,
            'education_class_id' => $educationClass->id,
            'registration_number' => 'REG-54321',
            'institution_name' => 'SMA Negeri 2 Jakarta',
            'institution_address' => 'Jl. Sudirman No. 10, Jakarta',
            'institution_phone' => '021-7654321',
            'institution_email' => 'sman2@jakarta.sch.id',
            'institution_website' => 'https://sman2.jakarta.sch.id',
            'institution_logo' => 'logo2.png',
            'institution_banner' => 'banner2.png',
            'institution_status' => 'inactive',
            'institution_description' => 'Sekolah Menengah Atas Negeri 2 Jakarta',
            'headmaster_id' => $headmaster->id,
        ];

        $response = $this->putJson("/api/main/educational-institution/{$educationalInstitution->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Institusi pendidikan berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('educational_institutions', [
            'id' => $educationalInstitution->id,
            'institution_name' => 'SMA Negeri 2 Jakarta',
            'registration_number' => 'REG-54321'
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_educational_institution()
    {
        $education = Education::factory()->create();
        $educationClass = EducationClass::factory()->create();
        $headmaster = Staff::factory()->create();

        $data = [
            'education_id' => $education->id,
            'education_class_id' => $educationClass->id,
            'institution_name' => 'Updated Institution',
            'institution_description' => 'Updated Description',
            'headmaster_id' => $headmaster->id,
        ];

        $response = $this->putJson('/api/main/educational-institution/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Institusi pendidikan tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_an_educational_institution()
    {
        $educationalInstitution = EducationalInstitution::factory()->create();

        $response = $this->deleteJson("/api/main/educational-institution/{$educationalInstitution->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Institusi pendidikan berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('educational_institutions', [
            'id' => $educationalInstitution->id
        ]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_educational_institution()
    {
        $response = $this->deleteJson('/api/main/educational-institution/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Institusi pendidikan tidak ditemukan'
            ]);
    }
}
