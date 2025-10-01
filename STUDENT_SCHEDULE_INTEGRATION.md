# Student Schedule Integration

## Overview

This document explains how student data is integrated with class schedule data in the application.

## Implementation Details

### How It Works

When retrieving class schedule data (either a single schedule or a list of schedules), the system now automatically includes student data for each schedule detail. The student data is retrieved based on the following matching criteria:

1. Educational Institution ID
2. Academic Year ID
3. Classroom ID
4. Class Group ID
5. Approval Status (must be "disetujui")

### Code Changes

#### ClassScheduleController Modifications

1. **Added Student Data Methods**:

    - `addStudentDataToSchedule($schedule)`: Adds student data to a single schedule
    - `addStudentDataToSchedules($schedules)`: Adds student data to multiple schedules

2. **Updated Controller Methods**:
    - `index()`: Now includes student data in the response
    - `show($id)`: Now includes student data in the response

### Data Structure

The student data is added directly to each schedule detail under a `students` property:

```json
{
    "message": "Data jadwal berhasil diambil",
    "status": 200,
    "data": {
        "details": [
            {
                "id": 1,
                "class_schedule_id": 1,
                "classroom_id": 1,
                "class_group_id": 1,
                "day": "senin",
                "lesson_hour_id": 1,
                "teacher_id": 1,
                "study_id": 1,
                "students": [
                    {
                        "id": 1,
                        "first_name": "John",
                        "last_name": "Doe"
                    }
                ]
            }
        ]
    }
}
```

### Technical Details

The student data is retrieved using the following query:

```php
$students = StudentClass::with('students')
    ->where('educational_institution_id', $schedule->educational_institution_id)
    ->where('academic_year_id', $schedule->academic_year_id)
    ->where('classroom_id', $detail->classroom_id)
    ->where('class_group_id', $detail->class_group_id)
    ->where('approval_status', 'disetujui')
    ->get()
    ->pluck('students');
```

This query:

1. Retrieves all StudentClass records that match the schedule criteria
2. Eager loads the related student data
3. Extracts only the student data using the `pluck` method
4. Adds the student data to the schedule detail

### Benefits

1. **Single API Call**: Student data is included in the same response as schedule data, eliminating the need for additional API calls.
2. **Automatic Filtering**: Only approved students are included in the response.
3. **Contextual Data**: Students are automatically matched to the correct schedule details based on institutional criteria.

### Testing

A new test file `ClassScheduleWithStudentsTest.php` has been created to verify that student data is correctly included in API responses.
