<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::where('name', 'superadmin')->first();

        if ($superadmin) {
            Staff::updateOrCreate(
                ['user_id' => $superadmin->id],
                [
                    'code' => 'SA-001',
                    'first_name' => 'Admin',
                    'last_name' => 'Pusat',
                    'email' => $superadmin->email,
                    'user_id' => $superadmin->id,
                ]
            );
        }
    }
}
