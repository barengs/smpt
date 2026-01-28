<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ParentProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class ParentImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create role if not exists
        if (!Role::where('name', 'orangtua')->where('guard_name', 'api')->exists()) {
            Role::create(['name' => 'orangtua', 'guard_name' => 'api']);
        }
    }

    /** @test */
    public function it_can_import_parents_successfully()
    {
        // Mock Excel file content or use a simplified approach
        // Since testing actual Excel parsing is complex without a real file,
        // we will trust Maatwebsite's parsing and focus on the Controller -> Import flow.
        
        // However, to really test my refactor (Optimized Role lookup), I need to trigger the Import class.
        // I will create a simple CSV for testing.
        
        $header = 'nik,kk,first_name,last_name,gender,parent_as,email,phone';
        $row1 = '1234567890123456,1234567890123456,Father,Doe,L,ayah,father@test.com,08123456789';
        $row2 = '1234567890123457,1234567890123457,Mother,Doe,P,ibu,mother@test.com,08123456788';
        
        $content = implode("\n", [$header, $row1, $row2]);
        
        $file = UploadedFile::fake()->createWithContent('parents.csv', $content);

        $response = $this->postJson('/api/main/parent/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.success_count', 2)
            ->assertJsonPath('data.failure_count', 0);

        $this->assertDatabaseHas('parent_profiles', ['nik' => '1234567890123456']);
        $this->assertDatabaseHas('users', ['email' => 'father@test.com']);

        // Verify login works with the optimized hash
        $token = auth()->attempt(['email' => 'father@test.com', 'password' => '1234567890123456']);
        $this->assertNotEmpty($token, 'Login failed or returned empty token');
    }

    /** @test */
    public function it_handles_duplicate_nik_in_import()
    {
        // Create an existing parent
        ParentProfile::factory()->create(['nik' => '9999999999999999']);

        $header = 'nik,kk,first_name,gender,parent_as';
        $row1 = '9999999999999999,1111111111111111,Duplicate,L,ayah'; // Duplicate NIK
        $row2 = '8888888888888888,2222222222222222,New,L,ayah';       // valid
        
        $content = implode("\n", [$header, $row1, $row2]);
        $file = UploadedFile::fake()->createWithContent('parents_duplicates.csv', $content);

        $response = $this->postJson('/api/main/parent/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
             ->assertJsonPath('data.success_count', 1)
             ->assertJsonPath('data.failure_count', 1);
    }
}
