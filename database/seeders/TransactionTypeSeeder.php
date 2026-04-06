<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'CASH-DEP',
                'name' => 'Setoran Tunai',
                'description' => 'Setoran Tunai di Teller',
                'category' => 'cash_operation',
                'is_debit' => false,
                'is_credit' => true,
                'default_debit_coa' => '1101', // Kas Teller
                'default_credit_coa' => '2100', // Tabungan Santri
                'is_active' => true,
            ],
            [
                'code' => 'CASH-WDR',
                'name' => 'Penarikan Tunai',
                'description' => 'Penarikan Tunai di Teller',
                'category' => 'cash_operation',
                'is_debit' => true,
                'is_credit' => false,
                'default_debit_coa' => '2100', // Tabungan Santri
                'default_credit_coa' => '1101', // Kas Teller
                'is_active' => true,
            ],
            [
                'code' => 'ADMIN-PAY',
                'name' => 'Pembayaran Administrasi',
                'description' => 'Pembayaran biaya admin/pendaftaran',
                'category' => 'payment',
                'is_debit' => true,
                'is_credit' => false,
                'default_debit_coa' => '2100',
                'default_credit_coa' => '4100', // Pendapatan Admin
                'is_active' => true,
            ],
            [
                'code' => 'REG-FEE',
                'name' => 'Biaya Pendaftaran',
                'description' => 'Pembayaran biaya pendaftaran santri baru',
                'category' => 'payment',
                'is_debit' => true,
                'is_credit' => false,
                'default_debit_coa' => '1101', // Kas Teller
                'default_credit_coa' => '4100', // Pendapatan Pendaftaran
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            \App\Models\TransactionType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
