# Presence Controller Implementation

## Overview

This document explains the implementation of the PresenceController with full CRUD functionality, statistics features, try-catch mechanisms, and Indonesian language responses.

## Implementation Details

### Controller Features

1. **Full CRUD Operations**:

    - `index()`: Retrieve all presences with filtering capabilities
    - `store()`: Create a new presence record
    - `show()`: Retrieve a specific presence by ID
    - `update()`: Update an existing presence record
    - `destroy()`: Delete a presence record

2. **Statistics Feature**:

    - `statistics()`: Get count of presences grouped by status (hadir, izin, sakit, alpha) with percentages

3. **Error Handling**:

    - All methods are wrapped in try-catch blocks
    - Specific handling for ValidationException, QueryException, ModelNotFoundException, and general Exception
    - Proper logging of errors using Laravel's Log facade

4. **Response Format**:
    - All responses use Indonesian language for messages
    - Consistent JSON structure with success flag, message, and data fields
    - Appropriate HTTP status codes

### Model Relationships

The Presence model includes the following relationships:

-   `student()`: Belongs to Student
-   `meetingSchedule()`: Belongs to MeetingSchedule
-   `user()`: Belongs to User

### Request Validation

The PresenceRequest class provides validation for:

-   Student ID (required, exists in students table)
-   Meeting Schedule ID (required, exists in meeting_schedules table)
-   Status (required, must be hadir/izin/sakit/alpha)
-   Description (optional, string, max 255 characters)
-   Date (optional, date format)
-   User ID (required, exists in users table)

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

-   Student ID
-   Meeting Schedule ID
-   Status
-   Date
-   User ID
-   Pagination (per_page parameter)

### Statistics Feature

The statistics method provides:

-   Count of presences for each status (hadir, izin, sakit, alpha)
-   Total count of all presences
-   Percentages for each status
-   Filtering capabilities similar to the index method

### Testing

A comprehensive test suite is included in `PresenceTest.php` that covers:

-   Creating presences
-   Retrieving presences
-   Updating presences
-   Deleting presences
-   Getting statistics
-   Validation requirements
