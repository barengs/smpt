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
        $file = __DIR__ . '/../../public/csv/orangtua_siswa.csv';
        $header = ['kk', 'nik', 'first_name', 'card_address'];
        $data = $Csv->csv_to_array($file, $header);

        // Prepare users data for batch insertion
        $users = [];
        foreach ($data as $item) {
            $users[] = [
                'name' => $item['first_name'],
                'email' => $item['nik'],
                'password' => Hash::make('password'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Batch insert users for better performance
        $userIds = [];
        $collection = collect($users);
        foreach ($collection->chunk(500) as $chunk) {
            DB::table('users')->insert($chunk->toArray());
        }

        // Get all inserted user IDs
        $allUsers = DB::table('users')
            ->orderBy('id', 'desc')
            ->limit(count($users))
            ->get(['id']);

        // Extract user IDs in the correct order
        $userIds = $allUsers->pluck('id')->reverse()->values()->toArray();

        // Prepare parent profiles data with user IDs
        $parentProfiles = [];
        foreach ($data as $index => $item) {
            $parentProfiles[] = [
                'kk' => $item['kk'],
                'nik' => $item['nik'],
                'first_name' => $item['first_name'],
                'card_address' => $item['card_address'],
                'user_id' => $userIds[$index] ?? null,
                'parent_as' => 'ayah', // Add default parent_as value
                'gender' => 'L', // Add default gender value
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Batch insert parent profiles
        $collection = collect($parentProfiles);
        foreach ($collection->chunk(500) as $chunk) {
            DB::table('parent_profiles')->insertOrIgnore($chunk->toArray());
        }

        // Get all parent profile user IDs and assign roles
        $parentUsers = DB::table('parent_profiles')->pluck('user_id');
        $userCollection = collect($parentUsers);

        foreach ($userCollection->chunk(500) as $chunk) {
            $users = User::whereIn('id', $chunk->toArray())->get();
            foreach ($users as $user) {
                $user->assignRole('orangtua');
            }
        }
    }
}
