<?php

namespace Tests\Feature;

use App\Models\ControlPanel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ControlPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_get_control_panel_data()
    {
        // When no record exists, it should create a default one
        $response = $this->getJson('/api/master/control-panel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data control panel berhasil diambil'
            ]);

        // Check that a record was created
        $this->assertEquals(1, ControlPanel::count());
    }

    /** @test */
    public function it_can_create_control_panel_data()
    {
        // Delete the default record if it was created
        ControlPanel::truncate();

        $data = [
            'app_name' => 'Test Application',
            'app_version' => '1.0.0',
            'app_description' => 'Test application description',
            'app_url' => 'https://testapp.com',
            'app_email' => 'test@testapp.com',
            'app_phone' => '1234567890',
            'app_address' => '123 Test Street',
            'is_maintenance_mode' => 'false',
            'app_theme' => 'light',
            'app_language' => 'indonesia',
        ];

        $response = $this->postJson('/api/master/control-panel', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data control panel berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('control_panels', $data);
    }

    /** @test */
    public function it_cannot_create_multiple_control_panel_records()
    {
        // Create first record
        ControlPanel::factory()->create();

        $data = [
            'app_name' => 'Second Application',
            'app_version' => '1.0.0',
            'is_maintenance_mode' => 'false',
            'app_theme' => 'light',
            'app_language' => 'indonesia',
        ];

        $response = $this->postJson('/api/master/control-panel', $data);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Data control panel sudah ada, gunakan fungsi update untuk mengubah data'
            ]);
    }

    /** @test */
    public function it_can_show_control_panel_data()
    {
        $controlPanel = ControlPanel::factory()->create();

        $response = $this->getJson("/api/master/control-panel/{$controlPanel->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data control panel berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_control_panel()
    {
        $response = $this->getJson('/api/master/control-panel/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data control panel tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_control_panel_data()
    {
        $controlPanel = ControlPanel::factory()->create();

        $data = [
            'app_name' => 'Updated Application',
            'app_version' => '2.0.0',
            'app_description' => 'Updated application description',
            'is_maintenance_mode' => 'true',
            'maintenance_message' => 'System under maintenance',
            'app_theme' => 'dark',
            'app_language' => 'english',
        ];

        $response = $this->putJson("/api/master/control-panel/{$controlPanel->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data control panel berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('control_panels', $data);
    }

    /** @test */
    public function it_can_update_control_panel_data_without_id()
    {
        $controlPanel = ControlPanel::factory()->create();

        $data = [
            'app_name' => 'Updated Application',
            'app_version' => '2.0.0',
            'is_maintenance_mode' => 'true',
            'app_theme' => 'dark',
            'app_language' => 'english',
        ];

        $response = $this->putJson('/api/master/control-panel', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data control panel berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('control_panels', $data);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_control_panel()
    {
        $data = [
            'app_name' => 'Updated Application',
            'app_version' => '2.0.0',
            'is_maintenance_mode' => 'true',
            'app_theme' => 'dark',
            'app_language' => 'english',
        ];

        $response = $this->putJson('/api/master/control-panel/999999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data control panel tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_control_panel_data()
    {
        $controlPanel = ControlPanel::factory()->create();

        $response = $this->deleteJson("/api/master/control-panel/{$controlPanel->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data control panel berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('control_panels', ['id' => $controlPanel->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_control_panel()
    {
        $response = $this->deleteJson('/api/master/control-panel/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data control panel tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_only_the_app_logo()
    {
        $controlPanel = ControlPanel::factory()->create();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png');

        $response = $this->postJson('/api/master/control-panel/logo', [
            'app_logo' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logo berhasil diperbarui'
            ]);

        // Assert that the file was stored
        Storage::disk('public')->assertExists('logos/' . $file->hashName());
    }

    /** @test */
    public function it_requires_logo_file_when_updating_logo()
    {
        $controlPanel = ControlPanel::factory()->create();

        $response = $this->postJson('/api/master/control-panel/logo', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_update_only_the_app_favicon()
    {
        $controlPanel = ControlPanel::factory()->create();

        Storage::fake('public');

        // Create a favicon with a valid extension
        $file = UploadedFile::fake()->image('favicon.png');

        $response = $this->postJson('/api/master/control-panel/favicon', [
            'app_favicon' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Favicon berhasil diperbarui'
            ]);

        // Assert that the file was stored
        Storage::disk('public')->assertExists('favicons/' . $file->hashName());
    }

    /** @test */
    public function it_requires_favicon_file_when_updating_favicon()
    {
        $controlPanel = ControlPanel::factory()->create();

        $response = $this->postJson('/api/master/control-panel/favicon', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }
}
