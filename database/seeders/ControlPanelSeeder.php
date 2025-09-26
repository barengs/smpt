<?php

namespace Database\Seeders;

use App\Models\ControlPanel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ControlPanelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ControlPanel::create([
            'app_name' => 'SIAP',
            'app_version' => '1.31.592',
            'app_description' => 'Aplikasi manajemen pesantren yang terintegrasi.',
            'app_logo' => null,
            'app_favicon' => 'favicon.ico',
            'app_url' => 'https://smp.umediatama.com',
            'app_email' => 'admin@umediatama.com',
            'app_phone' => '1234567890',
            'app_address' => 'Jl. Contoh, Kota Contoh, Negara Contoh',
        ]);
    }
}
