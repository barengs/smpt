<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'product_code' => 'TAB001',
                'product_name' => 'Tabungan Tibyan',
                'product_type' => 'Tabungan',
                'interest_rate' => 0,
                'admin_fee' => 0,
                'opening_fee' => 50000,
                'is_active' => true,
            ],
            [
                'product_code' => 'TAB002',
                'product_name' => 'Tabungan Kubar',
                'product_type' => 'Tabungan',
                'interest_rate' => 0,
                'admin_fee' => 0,
                'opening_fee' => 50000,
                'is_active' => true,
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['product_code' => $p['product_code']], $p);
        }
    }
}
