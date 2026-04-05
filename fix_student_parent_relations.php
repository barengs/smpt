<?php

use App\Models\Student;
use App\Models\ParentProfile;
use Illuminate\Support\Facades\DB;

/**
 * Script to fix broken student-parent relationships.
 * This script identifies students whose parent_id does not match any ParentProfile NIK,
 * and attempts to fix them by matching the KK (Kartu Keluarga) number.
 */

function fixStudentParentRelations() {
    echo "Starting relationship fix...\n";
    
    $students = Student::all();
    $fixedCount = 0;
    $alreadyCorrectCount = 0;
    $couldNotFixCount = 0;
    $noKkCount = 0;

    foreach ($students as $student) {
        // Check if relationship is already working
        if ($student->parents()->exists()) {
            $alreadyCorrectCount++;
            continue;
        }

        echo "Broken relation found for Student: {$student->first_name} (ID: {$student->id}, Current parent_id: {$student->parent_id})\n";

        if (empty($student->kk)) {
            echo "  - Skip: Student has no KK recorded.\n";
            $noKkCount++;
            $couldNotFixCount++;
            continue;
        }

        // Try to find a parent with the same KK
        $parent = ParentProfile::where('kk', $student->kk)->first();

        if ($parent) {
            echo "  + Match found via KK: {$parent->first_name} (NIK: {$parent->nik})\n";
            
            // Update the student's parent_id string to match the parent's NIK
            $oldParentId = $student->parent_id;
            $student->parent_id = $parent->nik;
            $student->save();
            
            echo "  + Fixed: Updated parent_id from '{$oldParentId}' to '{$parent->nik}'\n";
            $fixedCount++;
        } else {
            echo "  - No match found for KK: {$student->kk}\n";
            $couldNotFixCount++;
        }
    }

    echo "\n--- Summary ---\n";
    echo "Total Students: " . count($students) . "\n";
    echo "Already Correct: $alreadyCorrectCount\n";
    echo "Successfully Fixed: $fixedCount\n";
    echo "Could Not Fix: $couldNotFixCount (No KK or No matching parent KK)\n";
    echo "  - (No KK on student: $noKkCount)\n";
    echo "---------------\n";
}

// Check if running in Laravel environment
if (function_exists('app')) {
    fixStudentParentRelations();
} else {
    echo "Error: This script must be run within a Laravel environment (e.g., via php artisan tinker).\n";
}
