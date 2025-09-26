<?php

require_once 'vendor/autoload.php';

use Carbon\Carbon;

function getNextDateForDay($startDate, $day) {
    $daysMap = [
        'senin' => Carbon::MONDAY,
        'selasa' => Carbon::TUESDAY,
        'rabu' => Carbon::WEDNESDAY,
        'kamis' => Carbon::THURSDAY,
        'jumat' => Carbon::FRIDAY,
        'sabtu' => Carbon::SATURDAY,
        'minggu' => Carbon::SUNDAY,
    ];

    $targetDay = $daysMap[strtolower($day)] ?? Carbon::MONDAY;
    $currentDay = $startDate->dayOfWeek;

    if ($currentDay == $targetDay) {
        // Today is the target day, so start from next week
        $daysToAdd = 7;
    } elseif ($currentDay < $targetDay) {
        // Target day is later this week
        $daysToAdd = $targetDay - $currentDay;
    } else {
        // Target day is next week
        $daysToAdd = (7 - $currentDay) + $targetDay;
    }

    return $startDate->copy()->addDays($daysToAdd);
}

// Test cases
echo "Testing date calculation logic:\n\n";

// Test 1: Today is Monday, target is Wednesday
$startDate = Carbon::create(2025, 9, 29); // Monday
echo "Start date: " . $startDate->format('Y-m-d (l)') . "\n";
$nextDate = getNextDateForDay($startDate, 'rabu');
echo "Next Wednesday: " . $nextDate->format('Y-m-d (l)') . "\n\n";

// Test 2: Today is Wednesday, target is Monday (next week)
$startDate = Carbon::create(2025, 10, 1); // Wednesday
echo "Start date: " . $startDate->format('Y-m-d (l)') . "\n";
$nextDate = getNextDateForDay($startDate, 'senin');
echo "Next Monday: " . $nextDate->format('Y-m-d (l)') . "\n\n";

// Test 3: Today is Friday, target is Friday (next week)
$startDate = Carbon::create(2025, 10, 3); // Friday
echo "Start date: " . $startDate->format('Y-m-d (l)') . "\n";
$nextDate = getNextDateForDay($startDate, 'jumat');
echo "Next Friday: " . $nextDate->format('Y-m-d (l)') . "\n\n";

?>
