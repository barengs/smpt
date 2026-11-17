<?php

namespace App\Observers;

use App\Models\AcademicYear;
use App\Services\TarbiyahSyncService;

class AcademicYearObserver
{
    public function saved(AcademicYear $academicYear): void
    {
        dispatch(function () {
            TarbiyahSyncService::syncAcademicYears();
        })->afterResponse();
    }
}
