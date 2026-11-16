<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\TarbiyahSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncStudentToTarbiyahJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public bool $afterCommit = true;

    public function __construct(public int $studentId)
    {
    }

    public function handle(): void
    {
        $student = Student::find($this->studentId);
        if (! $student || empty($student->nis)) {
            return;
        }

        $ok = TarbiyahSyncService::syncStudent($student);
        if (! $ok) {
            throw new \RuntimeException("Gagal sinkronisasi santri ID {$this->studentId} (NIS: {$student->nis}) ke Tarbiyah.");
        }
    }
}
