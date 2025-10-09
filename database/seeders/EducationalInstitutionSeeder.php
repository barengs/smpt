<?php

namespace Database\Seeders;

use App\Models\EducationalInstitution;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EducationalInstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EducationalInstitution::create([
            'education_id' => 7,
            'education_class_id' => 1,
            'registration_number' => '123456789',
            'institution_name' => 'MI Panyeppen',
            'institution_address' => 'Jl. Panyeppen No. 1, Kec. Palengaan',
            'institution_phone' => '08123456789',
            'institution_email' => 'info@mi-panyeppen.sch.id',
            'institution_website' => 'www.mi-panyeppen.sch.id',
            'institution_status' => 'active',
            'institution_description' => 'Madrasah Ibtidaiyah',
            'headmaster_id' => 3,
        ]);
    }
}
