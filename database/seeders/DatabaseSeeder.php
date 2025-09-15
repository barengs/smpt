<?php

namespace Database\Seeders;

use App\Models\EducationClass;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravolt\Indonesia\Seeds\CitiesSeeder;
use Laravolt\Indonesia\Seeds\DistrictsSeeder;
use Laravolt\Indonesia\Seeds\ProvincesSeeder;
use Laravolt\Indonesia\Seeds\VillagesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            MenuSeeder::class,
            StaffSeeder::class,
            ControlPanelSeeder::class,
            ProvincesSeeder::class,
            CitiesSeeder::class,
            DistrictsSeeder::class,
            VillagesSeeder::class,
            ProfessionSeeder::class,
            ClassGroupSeeder::class,
            HostelSeeder::class,
            EducationClassSeeder::class,
            EducationSeeder::class,
            OccupationSeeder::class,
            ChartOfAccountSeeder::class,
            ParentProfileSeeder::class,
            StudentSeeder::class,
            StudySeeder::class,
            LessonHourSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
