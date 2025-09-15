<?php

namespace Tests\Feature;

use App\Models\TransactionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_transaction_types()
    {
        TransactionType::factory()->count(3)->create();

        $response = $this->getJson('/api/main/transaction-type');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data jenis transaksi berhasil diambil'
            ])
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'code', 'name', 'description']
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_transaction_type()
    {
        $data = [
            'code' => 'TT001',
            'name' => 'Transfer Tunai',
            'description' => 'Transaksi transfer tunai antar rekening',
            'category' => 'transfer',
            'is_debit' => true,
            'is_credit' => false,
            'default_debit_coa' => '101',
            'default_credit_coa' => '102',
            'is_active' => true
        ];

        $response = $this->postJson('/api/main/transaction-type', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Jenis transaksi berhasil dibuat'
            ]);

        $this->assertDatabaseHas('transaction_types', [
            'code' => 'TT001',
            'name' => 'Transfer Tunai'
        ]);
    }

    /** @test */
    public function it_can_show_a_transaction_type()
    {
        $transactionType = TransactionType::factory()->create();

        $response = $this->getJson("/api/main/transaction-type/{$transactionType->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data jenis transaksi berhasil ditemukan',
                'data' => [
                    'id' => $transactionType->id,
                    'code' => $transactionType->code,
                    'name' => $transactionType->name
                ]
            ]);
    }

    /** @test */
    public function it_can_update_a_transaction_type()
    {
        $transactionType = TransactionType::factory()->create();

        $updatedData = [
            'code' => 'TT002',
            'name' => 'Transfer Bank',
            'description' => 'Transaksi transfer antar bank',
            'category' => 'transfer',
            'is_debit' => true,
            'is_credit' => false,
            'default_debit_coa' => '103',
            'default_credit_coa' => '104',
            'is_active' => true
        ];

        $response = $this->putJson("/api/main/transaction-type/{$transactionType->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Jenis transaksi berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('transaction_types', [
            'id' => $transactionType->id,
            'code' => 'TT002',
            'name' => 'Transfer Bank'
        ]);
    }

    /** @test */
    public function it_can_delete_a_transaction_type()
    {
        $transactionType = TransactionType::factory()->create();

        $response = $this->deleteJson("/api/main/transaction-type/{$transactionType->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Jenis transaksi berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('transaction_types', [
            'id' => $transactionType->id
        ]);
    }
}
