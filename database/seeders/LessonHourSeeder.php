<?php

namespace Database\Seeders;

use App\Models\LessonHour;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LessonHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lessonHours = [
            [
                'name' => 'Jam Pelajaran 1',
                'start_time' => '07:00:00',
                'end_time' => '07:40:00',
                'order' => 1,
                'description' => 'Jam pelajaran pertama pagi',
            ],
            [
                'name' => 'Jam Pelajaran 2',
                'start_time' => '07:40:00',
                'end_time' => '08:20:00',
                'order' => 2,
                'description' => 'Jam pelajaran kedua pagi',
            ],
            [
                'name' => 'Jam Pelajaran 3',
                'start_time' => '08:20:00',
                'end_time' => '09:00:00',
                'order' => 3,
                'description' => 'Jam pelajaran ketiga pagi',
            ],
            [
                'name' => 'Istirahat',
                'start_time' => '09:00:00',
                'end_time' => '09:15:00',
                'order' => 4,
                'description' => 'Waktu istirahat pertama',
            ],
            [
                'name' => 'Jam Pelajaran 4',
                'start_time' => '09:15:00',
                'end_time' => '09:55:00',
                'order' => 5,
                'description' => 'Jam pelajaran keempat pagi',
            ],
            [
                'name' => 'Jam Pelajaran 5',
                'start_time' => '09:55:00',
                'end_time' => '10:35:00',
                'order' => 6,
                'description' => 'Jam pelajaran kelima pagi',
            ],
            [
                'name' => 'Istirahat',
                'start_time' => '10:35:00',
                'end_time' => '11:45:00',
                'order' => 7,
                'description' => 'Waktu istirahat makan siang',
            ],
            [
                'name' => 'Jam Pelajaran 6',
                'start_time' => '11:45:00',
                'end_time' => '12:25:00',
                'order' => 8,
                'description' => 'Jam pelajaran keenam siang',
            ],
            [
                'name' => 'Jam Pelajaran 7',
                'start_time' => '12:25:00',
                'end_time' => '13:05:00',
                'order' => 9,
                'description' => 'Jam pelajaran ketujuh siang',
            ],
        ];

        foreach ($lessonHours as $lessonHour) {
            LessonHour::updateOrCreate(
                ['name' => $lessonHour['name']],
                $lessonHour
            );
        }
    }
}
