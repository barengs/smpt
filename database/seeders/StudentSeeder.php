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
        $file = __DIR__ . '/../../public/csv/students.csv';
        $header = ['nis', 'created_at', 'period', 'nik', 'kk', 'first_name', 'born_in', 'born_at', 'last_education', 'address', 'village', 'district', 'postal_code', 'parent_id', 'phone', 'hostel_id', 'program_id', 'status', 'user_id'];
        $data = $Csv->csv_to_array($file, $header);
        $data = array_map(function ($arr) use ($now) {
            return $arr + ['updated_at' => $now];
        }, $data);
        $collection = collect($data);
        foreach ($collection->chunk(50) as $chunk) {
            DB::table('students')->insertOrIgnore($chunk->toArray());
        }
    }
}
