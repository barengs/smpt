<?php

namespace Tests\Feature;

use App\Models\EducationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_education_types()
    {
        EducationType::factory()->count(3)->create();

        $response = $this->getJson('/api/master/education-type');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => [
                    '*' => ['id', 'name', 'description', 'created_at', 'updated_at']
                ]
            ]);
    }

    /** @test */
    public function it_can_create_an_education_type()
    {
        $data = [
            'name' => 'University',
            'description' => 'Higher education institution'
        ];

        $response = $this->postJson('/api/master/education-type', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => ['id', 'name', 'description', 'created_at', 'updated_at']
            ]);

        $this->assertDatabaseHas('education_types', $data);
    }

    /** @test */
    public function it_can_show_an_education_type()
    {
        $educationType = EducationType::factory()->create();

        $response = $this->getJson("/api/master/education-type/{$educationType->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => ['id', 'name', 'description', 'created_at', 'updated_at']
            ]);
    }

    /** @test */
    public function it_can_update_an_education_type()
    {
        $educationType = EducationType::factory()->create();
        $updatedData = [
            'name' => 'Updated University',
            'description' => 'Updated higher education institution'
        ];

        $response = $this->putJson("/api/master/education-type/{$educationType->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => ['id', 'name', 'description', 'created_at', 'updated_at']
            ]);

        $this->assertDatabaseHas('education_types', $updatedData);
    }

    /** @test */
    public function it_can_delete_an_education_type()
    {
        $educationType = EducationType::factory()->create();

        $response = $this->deleteJson("/api/master/education-type/{$educationType->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Education type deleted successfully']);

        $this->assertSoftDeleted('education_types', ['id' => $educationType->id]);
    }

    /** @test */
    public function it_returns_404_when_education_type_not_found()
    {
        $response = $this->getJson('/api/master/education-type/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Education type not found']);
    }

    /** @test */
    public function it_can_list_trashed_education_types()
    {
        $educationType = EducationType::factory()->create();
        $educationType->delete();

        $response = $this->getJson('/api/master/education-type/trashed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'data' => [
                    '*' => ['id', 'name', 'description', 'deleted_at']
                ]
            ]);
    }

    /** @test */
    public function it_can_restore_a_trashed_education_type()
    {
        $educationType = EducationType::factory()->create();
        $educationType->delete();

        $response = $this->postJson("/api/master/education-type/{$educationType->id}/restore");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Education type restored successfully']);

        $this->assertDatabaseHas('education_types', ['id' => $educationType->id, 'deleted_at' => null]);
    }
}
