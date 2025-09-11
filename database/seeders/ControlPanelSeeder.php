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
            'app_name' => 'Sistem Manajemen Pesantren',
            'app_version' => '1.31.592',
            'app_description' => 'Aplikasi manajemen pesantren yang terintegrasi.',
            'app_logo' => 'logo.png',
            'app_favicon' => 'favicon.ico',
            'app_url' => 'https://smp.barengsaya.com',
            'app_email' => 'smp.barengsaya.com',
            'app_phone' => '1234567890',
            'app_address' => 'Jl. Contoh, Kota Contoh, Negara Contoh',
        ]);
    }
}
