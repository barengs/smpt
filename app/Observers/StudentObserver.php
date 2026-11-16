<?php

namespace App\Observers;

use App\Jobs\SyncStudentToTarbiyahJob;
use App\Models\Student;

class StudentObserver
{
    /** @var array<int, string> */
    private array $syncedFields = [
        'nis',
        'period',
        'program_id',
        'hostel_id',
        'first_name',
        'last_name',
        'gender',
        'status',
        'phone',
        'address',
        'nik',
        'born_in',
        'born_at',
        'parent_id',
        'photo',
    ];

    public function saved(Student $student): void
    {
        if (empty($student->nis)) {
            return;
        }

        if ($student->wasRecentlyCreated || $student->wasChanged($this->syncedFields)) {
            SyncStudentToTarbiyahJob::dispatch($student->id);
        }
    }
}
