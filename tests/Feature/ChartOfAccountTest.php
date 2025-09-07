<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountTest extends TestCase
{
    use RefreshDatabase;

    protected $parentCoa;
    protected $childCoa;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a parent COA
        $this->parentCoa = ChartOfAccount::create([
            'coa_code' => '1000',
            'account_name' => 'Aktiva',
            'account_type' => 'ASSET',
            'level' => 'header',
            'is_postable' => false,
            'is_active' => true,
        ]);

        // Create a child COA
        $this->childCoa = ChartOfAccount::create([
            'coa_code' => '1100',
            'account_name' => 'Kas',
            'account_type' => 'ASSET',
            'parent_coa_code' => '1000',
            'level' => 'detail',
            'is_postable' => true,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_list_all_chart_of_accounts()
    {
        $response = $this->getJson('/api/master/chart-of-account');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data chart of account berhasil diambil'
            ]);
    }

    /** @test */
    public function it_can_create_a_chart_of_account()
    {
        $data = [
            'coa_code' => '2000',
            'account_name' => 'Kewajiban',
            'account_type' => 'LIABILITY',
            'level' => 'header',
            'is_postable' => false,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/master/chart-of-account', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Chart of account berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('chart_of_accounts', $data);
    }

    /** @test */
    public function it_requires_coa_code_when_creating_a_chart_of_account()
    {
        $data = [
            'account_name' => 'Kewajiban',
            'account_type' => 'LIABILITY',
            'level' => 'header',
            'is_postable' => false,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/master/chart-of-account', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /** @test */
    public function it_can_show_a_chart_of_account()
    {
        $response = $this->getJson("/api/master/chart-of-account/{$this->parentCoa->coa_code}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data chart of account berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_chart_of_account()
    {
        $response = $this->getJson('/api/master/chart-of-account/9999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Chart of account tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_a_chart_of_account()
    {
        $data = [
            'account_name' => 'Aktiva Lancar',
            'account_type' => 'ASSET',
            'level' => 'subheader',
            'is_postable' => false,
            'is_active' => true,
        ];

        $response = $this->putJson("/api/master/chart-of-account/{$this->parentCoa->coa_code}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Chart of account berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('chart_of_accounts', [
            'coa_code' => $this->parentCoa->coa_code,
            'account_name' => 'Aktiva Lancar'
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_chart_of_account()
    {
        $data = [
            'account_name' => 'Equity',
            'account_type' => 'EQUITY',
            'level' => 'header',
            'is_postable' => false,
            'is_active' => true,
        ];

        $response = $this->putJson('/api/master/chart-of-account/9999', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Chart of account tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_a_chart_of_account()
    {
        // Create a COA without children for deletion
        $coaToDelete = ChartOfAccount::create([
            'coa_code' => '3000',
            'account_name' => 'Modal',
            'account_type' => 'EQUITY',
            'level' => 'header',
            'is_postable' => false,
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/master/chart-of-account/{$coaToDelete->coa_code}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Chart of account berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('chart_of_accounts', [
            'coa_code' => '3000'
        ]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_chart_of_account()
    {
        $response = $this->deleteJson('/api/master/chart-of-account/9999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Chart of account tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_cannot_delete_a_chart_of_account_with_children()
    {
        $response = $this->deleteJson("/api/master/chart-of-account/{$this->parentCoa->coa_code}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat menghapus chart of account yang memiliki anak'
            ]);
    }
}
