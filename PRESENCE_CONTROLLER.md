# Presence Controller Implementation

## Overview

This document explains the implementation of the PresenceController with full CRUD functionality, statistics features, try-catch mechanisms, and Indonesian language responses. The controller has been enhanced to integrate class schedule data with presence data through MeetingSchedule.

## Implementation Details

### Controller Features

1. **Full CRUD Operations**:

    - `index()`: Retrieve all presences with filtering capabilities
    - `store()`: Create a new presence record (automatically sets user_id and date)
    - `show()`: Retrieve a specific presence by ID
    - `update()`: Update an existing presence record
    - `destroy()`: Delete a presence record

2. **Enhanced Index Method**:

    - Default: Returns all presences with basic filtering
    - By Class Schedule: Returns complete class schedule data with students and presences
    - By Class Schedule Detail: Returns class schedule detail with students and presences
    - By Meeting Schedule: Returns meeting schedule with students and presences

3. **Statistics Feature**:

    - `statistics()`: Get count of presences grouped by status (hadir, izin, sakit, alpha) with percentages

4. **Error Handling**:

    - All methods are wrapped in try-catch blocks
    - Specific handling for ValidationException, QueryException, ModelNotFoundException, and general Exception
    - Proper logging of errors using Laravel's Log facade

5. **Response Format**:
    - All responses use Indonesian language for messages
    - Consistent JSON structure with success flag, message, and data fields
    - Appropriate HTTP status codes

### Model Relationships

The Presence model includes the following relationships:

-   `student()`: Belongs to Student
-   `meetingSchedule()`: Belongs to MeetingSchedule
-   `user()`: Belongs to User

### Store Method Enhancement

The store method has been enhanced to automatically:

-   Set `user_id` from the currently authenticated user (Auth::id())
-   Set `date` to the current date (now()->toDateString())
-   Only require `student_id`, `meeting_schedule_id`, and `status` from the request
-   Optionally accept `description` from the request

### Request Validation

The store method validates:

-   Student ID (required, exists in students table)
-   Meeting Schedule ID (required, exists in meeting_schedules table)
-   Status (required, must be hadir/izin/sakit/alpha)
-   Description (optional, string, max 255 characters)

The update method validates:

-   Student ID (sometimes required, exists in students table)
-   Meeting Schedule ID (sometimes required, exists in meeting_schedules table)
-   Status (sometimes required, must be hadir/izin/sakit/alpha)
-   Description (sometimes optional, string, max 255 characters)

### API Endpoints

The following routes are available:

-   `GET /api/main/presence` - Get all presences
-   `POST /api/main/presence` - Create a new presence
-   `GET /api/main/presence/{id}` - Get a specific presence
-   `PUT/PATCH /api/main/presence/{id}` - Update a presence
-   `DELETE /api/main/presence/{id}` - Delete a presence
-   `GET /api/main/presence/statistics` - Get presence statistics

### Filtering Capabilities

The index method supports filtering by:

-   Class Schedule ID (enhanced view with full schedule data)
-   Class Schedule Detail ID (enhanced view with detail data)
-   Meeting Schedule ID (enhanced view with meeting data)
-   Student ID
-   Status
-   Date
-   User ID

### Enhanced Index Method

The index method now provides three levels of data integration:

1. **Default View**: Returns all presence records with basic relationships
2. **Class Schedule View**: When `class_schedule_id` is provided, returns:
    - Complete class schedule information
    - All schedule details with classrooms, groups, etc.
    - Students enrolled in each class schedule detail
    - Meeting schedules for each detail
    - Presences for each meeting schedule
3. **Class Schedule Detail View**: When `class_schedule_detail_id` is provided, returns:
    - Class schedule detail information
    - Students enrolled in the class
    - Meeting schedules for the detail
    - Presences for each meeting schedule
4. **Meeting Schedule View**: When `meeting_schedule_id` is provided, returns:
    - Meeting schedule information
    - Students enrolled in the related class
    - Presences for the meeting schedule

### Statistics Feature

The statistics method provides:

-   Count of presences for each status (hadir, izin, sakit, alpha)
-   Total count of all presences
-   Percentages for each status
-   Filtering capabilities similar to the index method

### Testing

A comprehensive test suite is included in `PresenceTest.php` that covers:

-   Creating presences (with automatic user_id and date)
-   Retrieving presences (default and enhanced views)
-   Updating presences
-   Deleting presences
-   Getting statistics
-   Validation requirements
