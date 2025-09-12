<?php

namespace Database\Seeders;

use App\Models\Occupation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OccupationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $occupations = [
            'Petani',
            'Guru',
            'Dosen',
            'Wirausaha',
            'Karyawan Swasta',
            'Karyawan BUMN',
            'Karyawan BUMD',
            'PNS',
            'TNI',
            'Polri',
            'Dokter',
            'Perawat',
            'Apoteker',
            'Psikolog',
            'Psikiater',
            'Bidan',
            'Ahli Gizi',
            'Tenaga Medis Lainnya',
            'Tenaga Kesehatan Lainnya',
            'Ahli Hukum',
            'Notaris',
            'Pengacara',
            'Hakim',
            'Jaksa',
            'Tukang',
            'Buruh',
            'Nelayan',
            'Peternak',
            'Pedagang',
            'Buruh Harian Lepas',
            'Pekerja Lepas',
            'Pekerja Lepas Lainnya',
            'Pekerja Sosial',
            'Pekerja Sosial Masyarakat',
            'Pekerja Sosial Kesehatan',
            'Pekerja Sosial Pendidikan',
            'Pekerja Sosial Lingkungan',
        ];

        // Insert each occupation into the database
        foreach ($occupations as $occupation) {
            Occupation::create([
                'name' => $occupation,
            ]);
        }
    }
}
