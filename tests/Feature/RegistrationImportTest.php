<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Program;
use App\Models\AcademicYear;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RegistrationImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup authenticated user
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        
        // Setup academic year
        AcademicYear::create([
            'year' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'active' => true,
        ]);

        // Setup role
        \Spatie\Permission\Models\Role::create(['name' => 'orangtua', 'guard_name' => 'api']);
    }

    /** @test */
    public function it_can_import_registrations_with_english_headers()
    {
        $program = Program::factory()->create();

        $header = 'wali_nik,wali_nama_depan,wali_nama_belakang,wali_kk,wali_telepon,wali_email,wali_jenis_kelamin,wali_sebagai,wali_alamat_ktp,wali_alamat_domisili,wali_pekerjaan_id,wali_pendidikan_id,santri_nisn,santri_nama_depan,santri_nama_belakang,santri_nik,santri_jenis_kelamin,santri_alamat,santri_tempat_lahir,santri_tanggal_lahir,santri_desa_code,santri_telepon,santri_kode_pos,program_id,period,status';
        
        $row = "3528061508860021,Ahmad,Fauzi,3528061808810022,081234567890,fauzi@example.com,L,ayah,KTP,Domisili,1,1,2026001,Muhammad,Ali,3201010101010101,L,Alamat,Jakarta,2015-05-20,3201010001,081234567890,60111,{$program->id},2026,pending";

        $content = implode("\n", [$header, $row]);
        $file = UploadedFile::fake()->createWithContent('registrations_eng.csv', $content);

        $response = $this->postJson('/api/main/registration/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.success_count', 1)
            ->assertJsonPath('data.failure_count', 0);

        $this->assertDatabaseHas('registrations', [
            'nis' => '2026001',
            'first_name' => 'Muhammad',
            'parent_id' => '3528061508860021',
        ]);
    }

    /** @test */
    public function it_can_import_registrations_with_indonesian_headers_and_synonyms()
    {
        $program = Program::factory()->create();

        // Using Indonesian synonyms / variants
        $header = 'nik_wali,nama_depan_wali,nama_belakang_wali,kk_wali,telepon_wali,email_wali,jenis_kelamin_wali,hubungan_wali,alamat_ktp_wali,alamat_domisili_wali,pekerjaan_id_wali,pendidikan_id_wali,nisn_santri,nama_depan_santri,nama_belakang_santri,nik_santri,jenis_kelamin_santri,alamat_santri,tempat_lahir_santri,tanggal_lahir_santri,desa_code_santri,telepon_santri,kode_pos_santri,program_id,period,status';
        
        $row = "3528061508860021,Ahmad,Fauzi,3528061808810022,081234567890,fauzi@example.com,L,ayah,KTP,Domisili,1,1,2026001,Muhammad,Ali,3201010101010101,L,Alamat,Jakarta,2015-05-20,3201010001,081234567890,60111,{$program->id},2026,pending";

        $content = implode("\n", [$header, $row]);
        $file = UploadedFile::fake()->createWithContent('registrations_ind.csv', $content);

        $response = $this->postJson('/api/main/registration/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.success_count', 1)
            ->assertJsonPath('data.failure_count', 0);

        $this->assertDatabaseHas('registrations', [
            'nis' => '2026001',
            'first_name' => 'Muhammad',
            'parent_id' => '3528061508860021',
        ]);
    }

    /** @test */
    public function it_can_import_registrations_with_raw_database_export_format()
    {
        $program = Program::factory()->create();

        // Using raw database export columns
        $header = 'id,registration_number,registration_date,status,parent_id,nis,period,nik,kk,first_name,last_name,gender,address,born_in,born_at,village_id,postal_code,phone,photo,program_id,payment_status,payment_amount,previous_school,previous_school_address,certificate_number,education_level_id,previous_madrasah,previous_madrasah_address,certificate_madrasah,madrasah_level_id,deleted_at,created_at,updated_at,student_id,parent,program';
        
        $row = "177,REG2026162,,pending,3527140505870007,0148425432,,3527142004140002,3527141801180015,KANZUL,KAROMI,L,Alamat,Sampang,2014-04-20,3527142002,60111,081234567890,,{$program->id},payment_status,payment_amount,previous_school,previous_school_address,certificate_number,1,previous_madrasah,previous_madrasah_address,certificate_madrasah,1,,2026-06-10T06:21:29.000000Z,2026-06-10T06:21:29.000000Z,student_id,parent,program";

        $content = implode("\n", [$header, $row]);
        $file = UploadedFile::fake()->createWithContent('calon_santri_test.csv', $content);

        $response = $this->postJson('/api/main/registration/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.success_count', 1)
            ->assertJsonPath('data.failure_count', 0);

        $this->assertDatabaseHas('registrations', [
            'nis' => '0148425432',
            'first_name' => 'KANZUL',
            'parent_id' => '3527140505870007',
            'registration_number' => 'REG2026162',
            'kk' => '3527141801180015',
            'previous_school' => 'previous_school',
            'education_level_id' => '1',
            'previous_madrasah' => 'previous_madrasah',
            'madrasah_level_id' => '1',
        ]);

        $this->assertDatabaseHas('parent_profiles', [
            'nik' => '3527140505870007',
            'kk' => '3527141801180015',
        ]);
    }
}
