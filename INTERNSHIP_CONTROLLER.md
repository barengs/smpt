# Internship Controller Implementation

## Overview

This document explains the implementation of the InternshipController with full CRUD functionality using try-catch blocks and Indonesian language responses.

## Implementation Details

### Controller Features

1. **Full CRUD Operations**:

    - `index()`: Retrieve all internships with filtering capabilities
    - `store()`: Create a new internship record
    - `show()`: Retrieve a specific internship by ID
    - `update()`: Update an existing internship record
    - `destroy()`: Delete an internship record

2. **Error Handling**:

    - All methods are wrapped in try-catch blocks
    - Specific handling for ValidationException, QueryException, and general Exception
    - Proper logging of errors using Laravel's Log facade

3. **Response Format**:
    - All responses use Indonesian language for messages
    - Consistent JSON structure with success flag, message, and data fields
    - Appropriate HTTP status codes

### Model Relationships

The Internship model includes the following relationships:

-   `academicYear()`: Belongs to AcademicYear
-   `student()`: Belongs to Student
-   `supervisor()`: Belongs to InternshipSupervisor

### Request Validation

The InternshipRequest class provides validation for:

-   Academic year ID (required, exists in academic_years table)
-   Student ID (required, exists in students table)
-   Supervisor ID (required, exists in internship_supervisors table)
-   Status (optional, must be pending/approved/rejected)
-   File (optional, string, max 255 characters)
-   Long term (optional, integer)

### API Endpoints

The following routes are available:

-   `GET /api/main/internship` - Get all internships
-   `POST /api/main/internship` - Create a new internship
-   `GET /api/main/internship/{id}` - Get a specific internship
-   `PUT/PATCH /api/main/internship/{id}` - Update an internship
-   `DELETE /api/main/internship/{id}` - Delete an internship

### Filtering Capabilities

The index method supports filtering by:

-   Academic year ID
-   Student ID
-   Supervisor ID
-   Status
-   Pagination (per_page parameter)

### Testing

A comprehensive test suite is included in `InternshipTest.php` that covers:

-   Creating internships
-   Retrieving internships
-   Updating internships
-   Deleting internships
-   Validation requirements
