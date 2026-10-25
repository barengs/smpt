<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $Csv = new CsvtoArray();
        $file = __DIR__ . '/../../public/csv/students2.csv';
        $header = ['id', 'nis', 'created_at', 'period', 'nik', 'kk', 'first_name', 'born_in', 'born_at', 'last_education', 'address', 'village', 'district', 'postal_code', 'parent_id', 'phone', 'hostel_id', 'program_id', 'status', 'user_id'];
        $data = $Csv->csv_to_array($file, $header);
        $data = array_map(function ($arr) use ($now) {
            // Bersihkan data
            $bornAt = null;
            if (!empty($arr['born_at'])) {
                $parts = explode('/', trim($arr['born_at']));
                if (count($parts) === 3) {
                    $year = strlen($parts[2]) === 2 ? ('20' . $parts[2]) : $parts[2];
                    $bornAt = sprintf('%04d-%02d-%02d', (int)$year, (int)$parts[1], (int)$parts[0]);
                }
            }

            // Bersihkan phone
            $phone = !empty($arr['phone']) ? ltrim(trim($arr['phone']), "'") : null;
            if ($phone === '0822________' || $phone === '085_________') {
                $phone = null;
            }

            return [
                'id'             => (int) $arr['id'],
                'nis'            => trim($arr['nis']),
                'period'         => trim($arr['period']),
                'nik'            => trim($arr['nik']),
                'kk'             => trim($arr['kk']),
                'first_name'     => trim($arr['first_name']),
                'last_name'      => null,
                'gender'         => 'L', // 100% Banin / Putra (santri saat ini tidak ada perempuan)
                'born_in'        => trim($arr['born_in']),
                'born_at'        => $bornAt,
                'last_education' => trim($arr['last_education']),
                'address'        => trim($arr['address']),
                'village'        => trim($arr['village']),
                'district'       => trim($arr['district']),
                'postal_code'    => trim($arr['postal_code']),
                'parent_id'      => trim($arr['parent_id']),
                'phone'          => $phone,
                'status'         => 'Aktif',
                'user_id'        => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }, $data);
        $collection = collect($data);
        foreach ($collection->chunk(50) as $chunk) {
            DB::table('students')->insertOrIgnore($chunk->toArray());
        }
    }
}
