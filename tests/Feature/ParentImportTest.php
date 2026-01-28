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
    public function it_cleans_email_input_and_skips_duplicates()
    {
        // 1. Create existing parent for duplicate check
        ParentProfile::factory()->create(['nik' => '9999999999999999']);

        // 2. Prepare CSV content
        // Row 1: Duplicate NIK (Should be skipped)
        // Row 2: Valid, but email has whitespace (Should be cleaned)
        // Row 3: Valid, email is empty string (Should be null)
        
        $header = 'nik,kk,first_name,gender,parent_as,email';
        $row1 = '9999999999999999,1111111111111111,Duplicate,L,ayah,dup@test.com';
        $row2 = '8888888888888888,2222222222222222,DirtyEmail,L,ayah,   dirty@test.com   ';
        $row3 = '7777777777777777,3333333333333333,EmptyEmail,L,ayah,'; 

        $content = implode("\n", [$header, $row1, $row2, $row3]);
        $file = UploadedFile::fake()->createWithContent('parents_clean.csv', $content);

        $response = $this->postJson('/api/main/parent/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
             ->assertJsonPath('data.success_count', 2)
             ->assertJsonPath('data.skipped_count', 1) // Duplicate NIK
             ->assertJsonPath('data.failure_count', 0);

        // Check trimmed email
        $this->assertDatabaseHas('users', ['email' => 'dirty@test.com']);
        
        // Check empty email became NIK (default logic in Import) or null?
        // Logic: $email = !empty($row['email']) ? $row['email'] : $nik;
        // Since we set empty string to null in prepareForValidation, !empty(null) is false.
        // So it should default to NIK.
        $this->assertDatabaseHas('users', ['email' => '7777777777777777']);
    }
}
