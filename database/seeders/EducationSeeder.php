<?php

namespace Database\Seeders;

use App\Models\Education;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EducationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'TK', 'description' => 'Taman Kanak-Kanak'],
            ["name" => "SD", "description" => "Sekolah Dasar"],
            ["name" => "SMP", "description" => "Sekolah Menengah Pertama"],
            ["name" => "SMA", "description" => "Sekolah Menengah Atas"],
            ["name" => "SMK", "description" => "Sekolah Menengah Kejuruan"],
            ["name" => "MA", "description" => "Madrasah Aliyah"],
            ["name" => "MI", "description" => "Madrasah Ibtida'iyah"],
            ["name" => "MTs", "description" => "Madrasah Tsanawiyah"],
            ["name" => "MAK", "description" => "Madrasah Kejuruan"],
            ["name" => "PKBM", "description" => "Pusat Kegiatan Belajar Masyarakat"],
            ["name" => "S1", "description" => "Strata 1"],
            ["name" => "S2", "description" => "Strata 2"],
            ["name" => "S3", "description" => "Strata 3"],
        ];

        foreach ($data as $value) {
            $edu = Education::create([
                'name' => $value['name'],
                'description' => $value['description'],
            ]);
            // $edu->education_class()->attach($edu->id);
            DB::table('education_has_education_classes')->insert([
                'education_id' => $edu->id,
                'education_class_id' => 1,
            ]);
        }
    }
}
