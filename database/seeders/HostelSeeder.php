<?php

namespace Database\Seeders;

use App\Models\Hostel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HostelSeeder extends Seeder
{
    protected $datas = [
        ["name" => "Sunan Ampel"],
        ["name" => "Sunan Bonang"],
        ["name" => "Sunan Drajat"],
        ["name" => "Sunan Gunung Jati"],
        ["name" => "Sunan Giri"],
        ["name" => "Sunan Kudus"],
        ["name" => "Sunan Kali Jaga"],
        ["name" => "Sunan Maulana Malik Ibrohim"],
        ["name" => "Sunan Muria"],
        ["name" => "Sayyidina Ali"],
        ["name" => "Imam Ibnu Hajar Al-Haitami"],
        ["name" => "Imam An-Nawawi"],
        ["name" => "Imam As-Suyuthi"],
        ["name" => "Sayyidina Abu Bakar"],
        ["name" => "Imam Ar-Rofi'i"],
        ["name" => "Imam Sibaweh"],
        ["name" => "Imam Haramain"],
        ["name" => "Sayyidina Umar"],
        ["name" => "Sayyidina Utsman"],
        ["name" => "Imam Syafi'i"],
        ["name" => "Imam Ghazali"],
        ["name" => "Imam Maliki"],
        ["name" => "Imam Hanafi"],
        ["name" => "Imam Hambali"],
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->datas as $value) {
            Hostel::create($value);
        }
    }
}
