<?php

namespace Database\Seeders;

use App\Models\Study;
use Illuminate\Database\Seeder;

class StudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studies = [
            ['name' => 'Ilmu Nahwu', 'description' => 'Ilmu yang mempelajari tentang harokat dan tanda-tanda yang dibaca dalam bahasa arab'],
            ['name' => 'Ilmu Urooj', 'description' => 'Ilmu yang mempelajari tentang urusan perkahwinan dalam Islam'],
            ['name' => 'Ilmu Akhlaaq', 'description' => 'Ilmu yang mempelajari tentang peribahasa dan ajaran agama Islam tentang cara-cara bertingkah laku yang baik'],
            ['name' => 'Ilmu Usool Al-Qur\'an', 'description' => 'Ilmu yang mempelajari tentang asas-asas pengetahuan Al-Qur\'an'],
            ['name' => 'Ilmu Tafsir Al-Qur\'an', 'description' => 'Ilmu yang mempelajari tentang arti dan makna Al-Qur\'an'],
        ];

        foreach ($studies as $study) {
            Study::create($study);
        }
    }
}
