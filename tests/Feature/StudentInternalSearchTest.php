<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentInternalSearchTest extends TestCase
{
    use RefreshDatabase;
    public function test_can_search_students_with_internal_key()
    {
        $response = $this->withHeaders([
            'X-Internal-Key' => 'smpt-banksantri-internal-secret-2026',
            'Accept' => 'application/json',
        ])->getJson('/api/main/student?search=santri');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'status',
            'data' => [
                'data',
                'current_page',
            ],
        ]);
    }

    public function test_unauthenticated_request_does_not_crash()
    {
        $response = $this->getJson('/api/main/student?search=santri');
        $response->assertStatus(200);
    }
}
