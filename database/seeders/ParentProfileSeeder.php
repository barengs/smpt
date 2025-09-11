<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ParentProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $Csv = new CsvtoArray();
        $file = __DIR__ . '/../../public/csv/orangtua_siswa_ayah.csv';
        $header = ['kk', 'nik', 'first_name', 'card_address', 'user_id'];
        $data = $Csv->csv_to_array($file, $header);
        $data = array_map(function ($arr) use ($now) {
            return $arr + ['created_at' => $now, 'updated_at' => $now];
        }, $data);

        foreach ($data as $item) {
            $user = User::create([
                'name' => $item['first_name'],
                'email' => $item['nik'],
                'password' => Hash::make('password'),
            ]);

            $user->assignRole('orangtua');
        }

        $collection = collect($data);
        foreach ($collection->chunk(50) as $chunk) {
            DB::table('parent_profiles')->insertOrIgnore($chunk->toArray());
        }
    }
}
