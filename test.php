<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dets = \App\Models\ClassScheduleDetail::with(['classGroup', 'classroom', 'study', 'classSchedule', 'teacher'])->get();
foreach($dets as $d) {
    if (strpos(strtolower($d->study->name), 'fiqhiyyah') !== false || strpos(strtolower($d->classGroup->name), 'iii a') !== false) {
        echo "MATCHED ScheduleDetail ID: " . $d->id . " | Class: " . $d->classroom->name . " | Rombel: " . $d->classGroup->name . " (ID: " . $d->class_group_id . ")\n";
        echo "   Teacher: " . ($d->teacher->first_name ?? 'none') . "\n";
        echo "   Academic Year ID: " . $d->classSchedule->academic_year_id . "\n";
        
        $scs = \App\Models\StudentClass::where('class_group_id', $d->class_group_id)->count();
        echo "   Total StudentClass records for this group: " . $scs . "\n";
        
        $scsStrict = \App\Models\StudentClass::where('educational_institution_id', $d->classSchedule->educational_institution_id)
            ->where('academic_year_id', $d->classSchedule->academic_year_id)
            ->where('classroom_id', $d->classroom_id)
            ->where('class_group_id', $d->class_group_id)
            ->where('approval_status', 'disetujui')
            ->count();
        echo "   Total StudentClass via exact Presensi logic: " . $scsStrict . "\n";
    }
}
if (count($dets) === 0) echo "No Schedule details found.\n";
