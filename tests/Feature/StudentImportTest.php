<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Program;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Role;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup authenticated user
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_imports_students_successfully_and_handles_duplicates()
    {
        // 1. Setup Data
        $program = Program::factory()->create();
        
        // Create existing student for duplicate check
        Student::factory()->create([
            'nis' => '1001',
            'nik' => '3500000000000001',
            'kk'  => '3500000000009999',
            'first_name' => 'Existing',
        ]);

        // 2. Prepare CSV Content
        // Row 1: Duplicate NIS (Should be SKIPPED)
        // Row 2: Duplicate NIK, New NIS (Should be SKIPPED)
        // Row 3: Duplicate KK, New NIS, New NIK (Should be SUCCESS - Allowed)
        // Row 4: New Student (Should be SUCCESS)

        $header = 'nis,nik,kk,first_name,gender,program_id,parent_id,period,address,born_in,born_at,last_education,village_id,village,district,postal_code,phone,hostel_id,status';
        
        $row1 = "1001,3500000000000002,3500000000008888,DuplicateNIS,L,{$program->id},PARENT01,2025/2026,Alamat,SBY,2010-01-01,SD,1,Desa,Kec,61111,08123,1,Aktif"; 
        $row2 = "1002,3500000000000001,3500000000008888,DuplicateNIK,L,{$program->id},PARENT02,2025/2026,Alamat,SBY,2010-01-01,SD,1,Desa,Kec,61111,08123,1,Aktif"; 
        $row3 = "1003,3500000000000003,3500000000009999,DuplicateKK,L,{$program->id},PARENT03,2025/2026,Alamat,SBY,2010-01-01,SD,1,Desa,Kec,61111,08123,1,Aktif";  
        $row4 = "1004,3500000000000004,3500000000007777,NewStudent,L,{$program->id},PARENT04,2025/2026,Alamat,SBY,2010-01-01,SD,1,Desa,Kec,61111,08123,1,Aktif";   

        $content = implode("\n", [$header, $row1, $row2, $row3, $row4]);
        
        $file = UploadedFile::fake()->createWithContent('students_test.csv', $content);

        // 3. Execute Import
        $response = $this->postJson('/api/main/student/import', [
            'file' => $file,
        ]);

        dump($response->json());

        // 4. Verify Response
        $response->assertStatus(200)
            ->assertJsonPath('data.success_count', 2) // Row 3 & 4
            ->assertJsonPath('data.skipped_count', 2) // Row 1 & 2
            ->assertJsonPath('data.failure_count', 0);

        // 5. Verify Database
        // Row 1 (Dup NIS) - Should NOT create new record with same NIS (obviously) or update existing? Import usually ignores.
        // Logic checks existance and Skips.
        
        // Row 2 (Dup NIK) - Should be skipped
        $this->assertDatabaseMissing('students', ['nis' => '1002']);

        // Row 3 (Dup KK) - Should be created
        $this->assertDatabaseHas('students', [
            'nis' => '1003',
            'kk' => '3500000000009999' // Same KK as existing
        ]);

        // Row 4 (Valid) - Should be created
        $this->assertDatabaseHas('students', ['nis' => '1004']);
    }
}
