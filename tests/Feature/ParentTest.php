<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ParentProfile;
use App\Models\Education;
use App\Models\Occupation;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParentTest extends TestCase
{
    use RefreshDatabase;

    protected $parentUser;
    protected $parentProfile;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a parent user with profile
        $this->parentUser = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->parentProfile = ParentProfile::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'nik' => '1234567890123456',
            'kk' => '1234567890123456',
            'gender' => 'L',
            'parent_as' => 'ayah',
            'user_id' => $this->parentUser->id,
        ]);
    }

    /** @test */
    public function it_can_list_parents()
    {
        $response = $this->getJson('/api/main/parent');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'data ditemukan',
            ]);
    }

    /** @test */
    public function it_can_show_a_parent()
    {
        $response = $this->getJson("/api/main/parent/{$this->parentUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'data ditemukan',
            ]);
    }

    /** @test */
    public function it_can_show_a_parent_with_education_and_occupation()
    {
        // Create education and occupation
        $education = Education::factory()->create([
            'name' => 'S1 Teknik Informatika'
        ]);

        $occupation = Occupation::factory()->create([
            'name' => 'Software Engineer'
        ]);

        // Update parent profile with education and occupation
        $this->parentProfile->update([
            'education_id' => $education->id,
            'occupation_id' => $occupation->id,
        ]);

        $response = $this->getJson("/api/main/parent/{$this->parentUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'data ditemukan',
            ])
            ->assertJsonStructure([
                'data' => [
                    'parent' => [
                        'education',
                        'occupation'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_parent()
    {
        $response = $this->getJson('/api/main/parent/999999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'data tidak ditemukan',
            ]);
    }

    /** @test */
    public function it_can_update_a_parent_without_photo()
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'nik' => '1234567890123457',
            'kk' => '1234567890123457',
            'gender' => 'P',
            'parent_as' => 'ibu',
            'card_address' => 'Jl. Kartini No. 1',
            'domicile_address' => 'Jl. Merdeka No. 1',
            'phone' => '081234567890',
            'email' => 'jane@example.com',
            'occupation' => null,
            'education' => null,
        ];

        $response = $this->putJson("/api/main/parent/{$this->parentUser->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data orang tua berhasil diperbarui',
            ]);

        $this->assertDatabaseHas('parent_profiles', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'nik' => '1234567890123457',
            'kk' => '1234567890123457',
            'gender' => 'P',
            'parent_as' => 'ibu',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
        ]);
    }

    /** @test */
    public function it_cannot_update_parent_with_duplicate_kk()
    {
        // Create another parent with a different KK
        $otherParent = ParentProfile::factory()->create([
            'kk' => '6543210987654321',
        ]);

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'nik' => '1234567890123457',
            'kk' => '6543210987654321', // Same as other parent
            'gender' => 'P',
            'parent_as' => 'ibu',
            'card_address' => 'Jl. Kartini No. 1',
            'domicile_address' => 'Jl. Merdeka No. 1',
            'phone' => '081234567890',
            'email' => 'jane@example.com',
            'occupation' => null,
            'education' => null,
        ];

        $response = $this->putJson("/api/main/parent/{$this->parentUser->id}", $data);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'KK sudah digunakan oleh orang tua lain',
            ]);
    }

    /** @test */
    public function it_cannot_update_parent_with_duplicate_nik()
    {
        // Create another parent with a different NIK
        $otherParent = ParentProfile::factory()->create([
            'nik' => '6543210987654321',
        ]);

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'nik' => '6543210987654321', // Same as other parent
            'kk' => '1234567890123457',
            'gender' => 'P',
            'parent_as' => 'ibu',
            'card_address' => 'Jl. Kartini No. 1',
            'domicile_address' => 'Jl. Merdeka No. 1',
            'phone' => '081234567890',
            'email' => 'jane@example.com',
            'occupation' => null,
            'education' => null,
        ];

        $response = $this->putJson("/api/main/parent/{$this->parentUser->id}", $data);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'NIK sudah digunakan oleh orang tua lain',
            ]);
    }

    /** @test */
    public function it_can_update_a_parent_with_photo()
    {
        Storage::fake('public');

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'nik' => '1234567890123457',
            'kk' => '1234567890123457',
            'gender' => 'P',
            'parent_as' => 'ibu',
            'card_address' => 'Jl. Kartini No. 1',
            'domicile_address' => 'Jl. Merdeka No. 1',
            'phone' => '081234567890',
            'email' => 'jane@example.com',
            'occupation' => null,
            'education' => null,
            'photo' => UploadedFile::fake()->image('profile.jpg'),
        ];

        $response = $this->putJson("/api/main/parent/{$this->parentUser->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data orang tua berhasil diperbarui',
            ]);

        // Check database
        $this->assertDatabaseHas('parent_profiles', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_parent()
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'nik' => '1234567890123457',
            'kk' => '1234567890123457',
            'gender' => 'P',
            'parent_as' => 'ibu',
            'card_address' => 'Jl. Kartini No. 1',
            'domicile_address' => 'Jl. Merdeka No. 1',
            'phone' => '081234567890',
            'email' => 'jane@example.com',
            'occupation' => null,
            'education' => null,
        ];

        $response = $this->putJson('/api/main/parent/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Data orang tua tidak ditemukan',
            ]);
    }

    /** @test */
    public function it_returns_validation_error_when_updating_parent_without_required_fields()
    {
        $data = [
            // Missing required fields
        ];

        $response = $this->putJson("/api/main/parent/{$this->parentUser->id}", $data);

        $response->assertStatus(422);
    }
}
