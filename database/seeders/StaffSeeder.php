<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StaffSeeder extends Seeder
{
    protected $data = [
        ["code" => "AS002025", "first_name" => "Admin", "email" => "admin@gmail.com", "last_name" => "Admin", "user_id" => 1],
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Loop through each employee data and create a new Employee record
        foreach ($this->data as $employee) {
            Staff::create([
                'code' => $employee['code'],
                'first_name' => $employee['first_name'],
                'last_name' => $employee['last_name'],
                'email' => $employee['email'],
                'user_id' => $employee['user_id'],
            ]);
        }
    }
}
