<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StaffSeeder extends Seeder
{
    protected $data = [
        ["code" => "AS002025", "first_name" => "Admin", "email" => "admin@gmail.com", "last_name" => "Admin", "user_id" => 1],
        ["code" => "AS012025", "first_name" => "RAHMAN", "email" => "rahman@gmail.com", "last_name" => "FARUQ", "user_id" => 2],
        ["code" => "AS022025", "first_name" => "RUMHUL", "email" => "rumhul@gmail.com", "last_name" => "AMIN", "user_id" => 3],
        ["code" => "AS032025", "first_name" => "FATHUR", "email" => "fathur@gmail.com", "last_name" => "ROZI", "user_id" => 4],
        ["code" => "AS042025", "first_name" => "GHUFRON", "email" => "ghufron@gmail.com", "last_name" => "DPU", "user_id" => 5],
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
