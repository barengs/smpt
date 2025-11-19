<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisciplineSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Violation Categories
        $categories = [
            [
                'name' => 'Pelanggaran Ringan',
                'description' => 'Pelanggaran yang bersifat ringan dan dapat ditolerir',
                'severity_level' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Pelanggaran Sedang',
                'description' => 'Pelanggaran yang memerlukan perhatian khusus',
                'severity_level' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Pelanggaran Berat',
                'description' => 'Pelanggaran yang sangat serius dan memerlukan tindakan tegas',
                'severity_level' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('violation_categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Seed Violations
        $violations = [
            // Pelanggaran Ringan (category_id: 1)
            ['category_id' => 1, 'name' => 'Terlambat masuk kelas', 'description' => 'Datang terlambat ke kelas tanpa alasan yang jelas', 'point' => 5],
            ['category_id' => 1, 'name' => 'Tidak memakai atribut lengkap', 'description' => 'Tidak menggunakan seragam atau atribut dengan lengkap', 'point' => 5],
            ['category_id' => 1, 'name' => 'Tidak mengerjakan tugas', 'description' => 'Tidak menyelesaikan tugas yang diberikan', 'point' => 10],
            ['category_id' => 1, 'name' => 'Gaduh di kelas', 'description' => 'Membuat keributan saat pembelajaran berlangsung', 'point' => 10],

            // Pelanggaran Sedang (category_id: 2)
            ['category_id' => 2, 'name' => 'Bolos kelas', 'description' => 'Tidak masuk kelas tanpa keterangan', 'point' => 20],
            ['category_id' => 2, 'name' => 'Keluar asrama tanpa izin', 'description' => 'Meninggalkan asrama tanpa seizin pengurus', 'point' => 25],
            ['category_id' => 2, 'name' => 'Merokok', 'description' => 'Kedapatan merokok di lingkungan sekolah/asrama', 'point' => 30],
            ['category_id' => 2, 'name' => 'Berkelahi', 'description' => 'Terlibat perkelahian dengan siswa lain', 'point' => 30],

            // Pelanggaran Berat (category_id: 3)
            ['category_id' => 3, 'name' => 'Mencuri', 'description' => 'Mengambil barang milik orang lain tanpa izin', 'point' => 50],
            ['category_id' => 3, 'name' => 'Membawa barang terlarang', 'description' => 'Membawa narkoba, senjata tajam, atau barang terlarang lainnya', 'point' => 100],
            ['category_id' => 3, 'name' => 'Merusak fasilitas', 'description' => 'Merusak fasilitas sekolah/asrama dengan sengaja', 'point' => 50],
        ];

        foreach ($violations as $violation) {
            DB::table('violations')->insert(array_merge($violation, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Seed Sanctions
        $sanctions = [
            [
                'name' => 'Peringatan Lisan',
                'description' => 'Teguran lisan dari guru/pembina',
                'type' => 'peringatan',
                'duration_days' => null,
            ],
            [
                'name' => 'Peringatan Tertulis I',
                'description' => 'Surat peringatan tertulis pertama',
                'type' => 'peringatan',
                'duration_days' => null,
            ],
            [
                'name' => 'Peringatan Tertulis II',
                'description' => 'Surat peringatan tertulis kedua',
                'type' => 'peringatan',
                'duration_days' => null,
            ],
            [
                'name' => 'Skorsing 1 Hari',
                'description' => 'Tidak diperbolehkan mengikuti kegiatan belajar selama 1 hari',
                'type' => 'skorsing',
                'duration_days' => 1,
            ],
            [
                'name' => 'Skorsing 3 Hari',
                'description' => 'Tidak diperbolehkan mengikuti kegiatan belajar selama 3 hari',
                'type' => 'skorsing',
                'duration_days' => 3,
            ],
            [
                'name' => 'Skorsing 1 Minggu',
                'description' => 'Tidak diperbolehkan mengikuti kegiatan belajar selama 1 minggu',
                'type' => 'skorsing',
                'duration_days' => 7,
            ],
            [
                'name' => 'Pembinaan Khusus',
                'description' => 'Mengikuti program pembinaan khusus',
                'type' => 'pembinaan',
                'duration_days' => 14,
            ],
            [
                'name' => 'Kerja Sosial',
                'description' => 'Melakukan kerja sosial di lingkungan sekolah',
                'type' => 'pembinaan',
                'duration_days' => 7,
            ],
            [
                'name' => 'Denda Ringan',
                'description' => 'Denda untuk kerusakan atau kehilangan barang',
                'type' => 'denda',
                'duration_days' => null,
            ],
        ];

        foreach ($sanctions as $sanction) {
            DB::table('sanctions')->insert(array_merge($sanction, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
