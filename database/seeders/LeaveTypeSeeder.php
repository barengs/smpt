<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Pulang',
                'description' => 'Izin pulang ke rumah untuk keperluan keluarga atau pribadi',
                'requires_approval' => true,
                'max_duration_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Keluar Pesantren',
                'description' => 'Izin keluar dari lingkungan pesantren untuk urusan tertentu',
                'requires_approval' => true,
                'max_duration_days' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Sakit',
                'description' => 'Izin karena kondisi kesehatan/sakit yang memerlukan perawatan',
                'requires_approval' => true,
                'max_duration_days' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'Berobat',
                'description' => 'Izin untuk berobat ke rumah sakit atau klinik',
                'requires_approval' => true,
                'max_duration_days' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Keperluan Keluarga',
                'description' => 'Izin untuk keperluan mendesak keluarga (hajatan, takziah, dll)',
                'requires_approval' => true,
                'max_duration_days' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Keperluan Pribadi',
                'description' => 'Izin untuk keperluan pribadi yang mendesak',
                'requires_approval' => true,
                'max_duration_days' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Mengikuti Kegiatan',
                'description' => 'Izin untuk mengikuti kegiatan di luar pesantren (lomba, seminar, dll)',
                'requires_approval' => true,
                'max_duration_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'description' => 'Izin untuk keperluan lain yang tidak termasuk kategori di atas',
                'requires_approval' => true,
                'max_duration_days' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['name' => $leaveType['name']],
                $leaveType
            );
        }

        $this->command->info('Leave types seeded successfully!');
    }
}
