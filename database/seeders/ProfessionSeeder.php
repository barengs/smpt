<?php

namespace Database\Seeders;

use App\Models\Profession;
use Illuminate\Database\Seeder;

class ProfessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $professions = [
            ['name' => 'Teacher', 'description' => 'Educates students in various subjects'],
            ['name' => 'Doctor', 'description' => 'Medical professional who diagnoses and treats patients'],
            ['name' => 'Engineer', 'description' => 'Applies scientific principles to design and build systems'],
            ['name' => 'Lawyer', 'description' => 'Legal professional who advises and represents clients'],
            ['name' => 'Nurse', 'description' => 'Healthcare professional who cares for patients'],
            ['name' => 'Accountant', 'description' => 'Manages and analyzes financial records'],
            ['name' => 'Software Developer', 'description' => 'Creates and maintains software applications'],
            ['name' => 'Chef', 'description' => 'Prepares and cooks food in restaurants or other establishments'],
            ['name' => 'Artist', 'description' => 'Creates visual or performing art'],
            ['name' => 'Writer', 'description' => 'Creates written content such as books, articles, or scripts'],
        ];

        foreach ($professions as $profession) {
            Profession::create($profession);
        }
    }
}
