<?php

namespace Database\Seeders;

use App\Models\EducationClass;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EducationClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ["code" => "EC001", "name" => "Formal"],
            ["code" => "EC002", "name" => "Non Formal"],
        ];

        foreach ($data as $value) {
            EducationClass::create($value);
        }
    }
}
