<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Student;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $product;
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a student
        $this->student = Student::factory()->create([
            'nis' => '1234567890'
        ]);

        // Create a product
        $this->product = Product::factory()->create();

        // Create an account
        $this->account = Account::create([
            'account_number' => $this->student->nis,
            'customer_id' => $this->student->id,
            'product_id' => $this->product->id,
            'balance' => 0,
            'status' => 'TIDAK AKTIF',
            'open_date' => now(),
        ]);
    }

    /** @test */
    public function it_can_list_all_accounts()
    {
        $response = $this->getJson('/api/main/account');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data akun berhasil diambil'
            ]);
    }

    /** @test */
    public function it_can_create_an_account()
    {
        // Create another student for this test
        $student = Student::factory()->create([
            'nis' => '0987654321'
        ]);

        $data = [
            'student_id' => $student->id,
            'product_id' => $this->product->id,
        ];

        $response = $this->postJson('/api/main/account', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Akun berhasil dibuat'
            ]);

        $this->assertDatabaseHas('accounts', [
            'account_number' => $student->nis,
            'customer_id' => $student->id,
            'product_id' => $this->product->id,
            'status' => 'TIDAK AKTIF'
        ]);
    }

    /** @test */
    public function it_requires_student_and_product_when_creating_an_account()
    {
        $data = [];

        $response = $this->postJson('/api/main/account', $data);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_cannot_create_account_for_student_who_already_has_one()
    {
        $data = [
            'student_id' => $this->student->id,
            'product_id' => $this->product->id,
        ];

        $response = $this->postJson('/api/main/account', $data);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Siswa sudah memiliki akun'
            ]);
    }

    /** @test */
    public function it_can_show_an_account()
    {
        $response = $this->getJson("/api/main/account/{$this->account->account_number}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data akun berhasil diambil'
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_account()
    {
        $response = $this->getJson('/api/main/account/nonexistent');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Akun tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_an_account()
    {
        $data = [
            'product_id' => $this->product->id,
            'status' => 'AKTIF',
        ];

        $response = $this->putJson("/api/main/account/{$this->account->account_number}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Akun berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('accounts', [
            'account_number' => $this->account->account_number,
            'status' => 'AKTIF'
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_account()
    {
        $data = [
            'product_id' => $this->product->id,
            'status' => 'AKTIF',
        ];

        $response = $this->putJson('/api/main/account/nonexistent', $data);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Akun tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_delete_an_account()
    {
        // Create a new account for deletion (without movements)
        $student = Student::factory()->create([
            'nis' => '1111111111'
        ]);

        $accountToDelete = Account::create([
            'account_number' => $student->nis,
            'customer_id' => $student->id,
            'product_id' => $this->product->id,
            'balance' => 0,
            'status' => 'TIDAK AKTIF',
            'open_date' => now(),
        ]);

        $response = $this->deleteJson("/api/main/account/{$accountToDelete->account_number}");

        $response->assertStatus(204); // Our controller returns 204 for successful deletion

        $this->assertDatabaseMissing('accounts', [
            'account_number' => $student->nis
        ]);
    }

    /** @test */
    public function it_cannot_delete_an_account_with_balance()
    {
        // Update account to have balance
        $this->account->update(['balance' => 1000]);

        $response = $this->deleteJson("/api/main/account/{$this->account->account_number}");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun dengan saldo aktif'
            ]);
    }

    /** @test */
    public function it_can_update_account_status()
    {
        $data = [
            'status' => 'AKTIF',
        ];

        $response = $this->putJson("/api/main/account/{$this->account->account_number}/status", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Status akun berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('accounts', [
            'account_number' => $this->account->account_number,
            'status' => 'AKTIF'
        ]);
    }

    /** @test */
    public function it_cannot_close_account_with_balance()
    {
        // Update account to have balance
        $this->account->update(['balance' => 1000]);

        $data = [
            'status' => 'TUTUP',
        ];

        $response = $this->putJson("/api/main/account/{$this->account->account_number}/status", $data);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat mengubah status menjadi TUTUP dengan saldo aktif'
            ]);
    }
}
