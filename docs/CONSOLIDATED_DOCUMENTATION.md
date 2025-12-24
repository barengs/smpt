# Consolidated Documentation

This file contains all project documentation that was previously in separate markdown files in the root directory.

## Internship Controller Implementation

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

---

## Presence Controller Implementation

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

---

## Quick Start - Student Leave System

# Quick Start - Student Leave System

## Setup

1. **Run Migration:**

```bash
php artisan migrate --path=database/migrations/2025_12_08_000001_create_student_leave_system_tables.php
```

2. **Seed Leave Types:**

```bash
php artisan db:seed --class=LeaveTypeSeeder
```

## Flow Penggunaan

### 1. Santri Mengajukan Izin

```
POST /api/student-leave
{
  "student_id": 1,
  "leave_type_id": 1,
  "start_date": "2025-12-15",
  "end_date": "2025-12-17",
  "reason": "Keperluan keluarga",
  "destination": "Alamat tujuan",
  "contact_person": "Nama kontak",
  "contact_phone": "08123456789"
}
```

Status: `pending`

### 2. Staff Approve/Reject

```
POST /api/student-leave/{id}/approve
{
  "approval_notes": "Disetujui"
}
```

Status: `approved`

### 3. Santri Lapor Kembali

```
POST /api/student-leave/{id}/submit-report
{
  "report_date": "2025-12-18",
  "condition": "sehat"
}
```

**Jika tepat waktu:** Status `completed`
**Jika terlambat:** Status `overdue` + penalti otomatis (5 point/hari)

### 4. Lihat Rekap

```
GET /api/student-leave/statistics
GET /api/student-leave/student/{studentId}/report
```

## Key Features

✅ Pengajuan izin dengan berbagai jenis
✅ Approval system (approve/reject)
✅ Pelaporan kembali wajib
✅ Penalti otomatis jika terlambat
✅ Rekap & statistik lengkap
✅ Support untuk berbagai status

## Status Flow

```
PENDING → APPROVED → ACTIVE → COMPLETED
                                  ↓
                              OVERDUE (+ penalty)
```

Lihat [STUDENT_LEAVE_SYSTEM.md](STUDENT_LEAVE_SYSTEM.md) untuk dokumentasi lengkap.

---

## Student Leave System API Documentation

# Student Leave System API Documentation

Sistem perizinan santri yang lengkap dengan approval, pelaporan, dan penalti.

## Fitur Utama

1. **Jenis Izin (Leave Types)** - Master data jenis-jenis izin
2. **Pengajuan Izin** - Santri mengajukan izin dengan durasi dan alasan
3. **Approval System** - Persetujuan/penolakan izin oleh staff
4. **Pelaporan Kembali** - Santri wajib lapor setelah izin berakhir
5. **Penalti System** - Otomatis memberikan penalti jika terlambat lapor
6. **Statistik & Rekap** - Laporan lengkap perizinan

---

## Endpoints

### 1. Leave Type Management (Master Data)

#### GET /api/master/leave-type

List semua jenis izin

-   **Query Params:**
    -   `is_active`: boolean (optional)

#### POST /api/master/leave-type

Buat jenis izin baru

-   **Body:**
    ```json
    {
        "name": "Pulang",
        "description": "Izin pulang ke rumah",
        "requires_approval": true,
        "max_duration_days": 7,
        "is_active": true
    }
    ```

#### GET /api/master/leave-type/{id}

Detail jenis izin

#### PUT /api/master/leave-type/{id}

Update jenis izin

#### DELETE /api/master/leave-type/{id}

Hapus jenis izin

---

### 2. Student Leave Management

#### GET /api/student-leave

List semua pengajuan izin dengan filter

-   **Query Params:**
    -   `student_id`: integer (optional)
    -   `leave_type_id`: integer (optional)
    -   `status`: enum - pending|approved|rejected|active|completed|overdue|cancelled (optional)
    -   `academic_year_id`: integer (optional)
    -   `start_date`: date (optional)
    -   `end_date`: date (optional)
    -   `per_page`: integer (optional, default 15)

#### POST /api/student-leave

Buat pengajuan izin baru

-   **Body:**
    ```json
    {
        "student_id": 1,
        "leave_type_id": 1,
        "academic_year_id": 1,
        "start_date": "2025-12-10",
        "end_date": "2025-12-12",
        "reason": "Keperluan keluarga yang mendesak",
        "destination": "Alamat rumah",
        "contact_person": "Nama wali",
        "contact_phone": "08123456789",
        "notes": "Catatan tambahan"
    }
    ```
-   **Response:** Status `pending`, menunggu approval

#### GET /api/student-leave/{id}

Detail pengajuan izin

#### PUT /api/student-leave/{id}

Update pengajuan izin (hanya status pending)

-   **Body:** Field yang ingin diubah (partial update)

#### DELETE /api/student-leave/{id}

Batalkan izin (status pending/approved)

---

### 3. Approval Endpoints

#### POST /api/student-leave/{id}/approve

Setujui pengajuan izin

-   **Body:**
    ```json
    {
        "approval_notes": "Disetujui" // optional
    }
    ```
-   **Response:** Status berubah menjadi `approved`

#### POST /api/student-leave/{id}/reject

Tolak pengajuan izin

-   **Body:**
    ```json
    {
        "approval_notes": "Alasan penolakan harus minimal 10 karakter" // required
    }
    ```
-   **Response:** Status berubah menjadi `rejected`

---

### 4. Leave Report (Laporan Kembali)

#### POST /api/student-leave/{id}/submit-report

Lapor kembali setelah izin berakhir

-   **Body:**
    ```json
    {
        "report_date": "2025-12-13",
        "report_time": "10:30", // optional
        "report_notes": "Catatan saat lapor",
        "condition": "sehat", // enum: sehat|sakit|lainnya
        "reported_to": 1 // staff_id (optional)
    }
    ```
-   **Response:**
    -   Jika tepat waktu: status `completed`
    -   Jika terlambat: status `overdue` + otomatis buat penalti
    -   Include: `is_late`, `late_days`, detail penalti jika ada

**Auto Penalty:**

-   Sistem otomatis deteksi keterlambatan
-   Point penalti: 5 point per hari keterlambatan
-   Penalty type: `peringatan`

---

### 5. Penalty Management

#### POST /api/student-leave/{id}/assign-penalty

Berikan penalti manual pada izin

-   **Body:**
    ```json
    {
        "penalty_type": "sanksi", // enum: peringatan|sanksi|poin
        "description": "Deskripsi penalti",
        "point_value": 20, // optional
        "sanction_id": 1 // optional, jika penalty_type = sanksi
    }
    ```

---

### 6. Statistics & Reports

#### GET /api/student-leave/statistics

Statistik perizinan keseluruhan

-   **Query Params:**

    -   `academic_year_id`: integer (optional)
    -   `student_id`: integer (optional)
    -   `start_date`: date (optional)
    -   `end_date`: date (optional)

-   **Response:**
    ```json
    {
        "success": true,
        "data": {
            "total_leaves": 150,
            "status_breakdown": {
                "pending": 10,
                "approved": 30,
                "completed": 80,
                "overdue": 15,
                "rejected": 10,
                "cancelled": 5
            },
            "leave_type_breakdown": [
                { "name": "Pulang", "total": 50 },
                { "name": "Sakit", "total": 30 }
            ],
            "overdue_count": 15,
            "with_penalty_count": 20,
            "monthly_trend": [
                { "year": 2025, "month": 12, "total": 25 },
                { "year": 2025, "month": 11, "total": 30 }
            ],
            "top_students": [
                {
                    "id": 1,
                    "first_name": "Ahmad",
                    "last_name": "Ali",
                    "nis": "12345",
                    "total_leaves": 8
                }
            ]
        }
    }
    ```

#### GET /api/student-leave/student/{studentId}/report

Laporan lengkap izin per santri

-   **Query Params:**

    -   `academic_year_id`: integer (optional)

-   **Response:**
    ```json
    {
        "success": true,
        "data": {
            "summary": {
                "total_leaves": 5,
                "approved_leaves": 4,
                "rejected_leaves": 1,
                "completed_leaves": 3,
                "overdue_leaves": 1,
                "total_days_on_leave": 15,
                "total_penalties": 1
            },
            "leaves": [
                /* array of leaves */
            ]
        }
    }
    ```

---

### 7. Cron Job Endpoint

#### POST /api/student-leave/check-overdue

Check dan update status izin yang overdue (untuk dijadwalkan via cron)

-   **Response:** Jumlah izin yang diupdate

**Cara Setup Cron:**

```bash
# Jalankan setiap hari jam 00:01
1 0 * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Status Flow

```
PENDING → APPROVED → ACTIVE → COMPLETED
    ↓         ↓                    ↓
REJECTED  CANCELLED         OVERDUE (jika terlambat lapor)
```

### Status Explanation:

-   **pending**: Menunggu approval
-   **approved**: Disetujui, siap digunakan
-   **rejected**: Ditolak
-   **active**: Sedang berlangsung (saat tanggal mulai tiba)
-   **completed**: Selesai (sudah lapor tepat waktu)
-   **overdue**: Terlambat lapor
-   **cancelled**: Dibatalkan

---

## Business Rules

1. **Pengajuan Izin:**

    - Tanggal mulai tidak boleh kurang dari hari ini
    - Durasi dihitung otomatis dari start_date dan end_date
    - Expected return date = end_date + 1 hari

2. **Approval:**

    - Hanya izin status `pending` yang bisa di-approve/reject
    - Penolakan harus disertai alasan minimal 10 karakter

3. **Update Izin:**

    - Hanya izin status `pending` yang bisa diubah
    - Jika mengubah tanggal, durasi dihitung ulang otomatis

4. **Pembatalan:**

    - Hanya izin status `pending` atau `approved` yang bisa dibatalkan

5. **Pelaporan:**

    - Hanya izin status `approved`, `active`, atau `overdue` yang bisa dilaporkan
    - Satu izin hanya bisa dilaporkan sekali
    - Sistem otomatis deteksi keterlambatan

6. **Penalti Otomatis:**
    - Jika lapor terlambat dari expected_return_date
    - Point: 5 per hari keterlambatan
    - Type: peringatan
    - Bisa ditambah penalti manual lainnya

---

## Migration & Seeding

### Run Migration:

```bash
php artisan migrate --path=database/migrations/2025_12_08_000001_create_student_leave_system_tables.php
```

### Seed Leave Types:

```bash
php artisan db:seed --class=LeaveTypeSeeder
```

### Default Leave Types:

1. Pulang (max 7 hari)
2. Keluar Pesantren (max 3 hari)
3. Sakit (max 14 hari)
4. Berobat (max 3 hari)
5. Keperluan Keluarga (max 5 hari)
6. Keperluan Pribadi (max 3 hari)
7. Mengikuti Kegiatan (max 7 hari)
8. Lainnya (max 5 hari)

---

## Response Format

### Success Response:

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        /* response data */
    }
}
```

### Error Response:

```json
{
    "success": false,
    "message": "Error message",
    "error": "Detailed error",
    "errors": {
        /* validation errors */
    }
}
```

---

## Example Use Cases

### 1. Santri Mengajukan Izin Pulang

```http
POST /api/student-leave
{
  "student_id": 1,
  "leave_type_id": 1, // Pulang
  "start_date": "2025-12-15",
  "end_date": "2025-12-17",
  "reason": "Ada acara keluarga yang penting",
  "destination": "Jl. Raya Bandung No. 123",
  "contact_person": "Ibu Siti",
  "contact_phone": "081234567890"
}
```

### 2. Staff Menyetujui Izin

```http
POST /api/student-leave/5/approve
{
  "approval_notes": "Disetujui, semoga lancar"
}
```

### 3. Santri Lapor Kembali (Tepat Waktu)

```http
POST /api/student-leave/5/submit-report
{
  "report_date": "2025-12-18",
  "condition": "sehat",
  "report_notes": "Sudah kembali dengan selamat"
}
// Response: status = completed, is_late = false
```

### 4. Santri Lapor Kembali (Terlambat 2 hari)

```http
POST /api/student-leave/5/submit-report
{
  "report_date": "2025-12-20",
  "condition": "sehat",
  "report_notes": "Maaf terlambat"
}
// Response:
// - status = overdue
// - is_late = true
// - late_days = 2
// - Auto penalty created: 10 point (2 days × 5 point)
```

### 5. Lihat Rekap Izin Santri

```http
GET /api/student-leave/student/1/report?academic_year_id=1
// Response: Summary + detail semua izin santri
```

---

## Testing

### Create Test Data:

```php
// Create leave types
LeaveType::factory(5)->create();

// Create student leaves
StudentLeave::factory()
    ->approved()
    ->count(10)
    ->create();

// Create overdue leaves with reports
StudentLeave::factory()
    ->overdue()
    ->count(5)
    ->create()
    ->each(function ($leave) {
        StudentLeaveReport::factory()
            ->late()
            ->create(['student_leave_id' => $leave->id]);
    });
```

---

## Database Tables

1. **leave_types** - Jenis izin
2. **student_leaves** - Pengajuan izin
3. **student_leave_reports** - Laporan kembali
4. **student_leave_penalties** - Penalti keterlambatan

Semua tabel memiliki relationship yang lengkap dan optimal indexing.

---

## Student Schedule Integration

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

---

## Registration Transaction Method Fix

# Registration Transaction Method Fix

## Issue

The `createRequestTransaction` method in the RegistrationController was not accessible from outside because:

1. The route was incorrectly registered under `TransactionController` instead of `RegistrationController`
2. There was a minor issue with the validation rules (unused `amount` field)

## Solution

1. Fixed the route registration in `routes/api.php`:

    - Changed from: `Route::post('registration/transaction', [TransactionController::class, 'createRequestTransaction'])`
    - Changed to: `Route::post('registration/transaction', [RegistrationController::class, 'createRequestTransaction'])`

2. Removed the unused `amount` validation rule from the method

3. Fixed the Auth facade usage to use the fully qualified class name to avoid linter errors

## Method Functionality

The `createRequestTransaction` method now:

1. Validates required input parameters
2. Creates a student record from registration data
3. Creates an account for the student
4. Creates a transaction record
5. Creates transaction ledger entries
6. Updates the registration with payment status

## Route

The method is now accessible via:
`POST /api/main/registration/transaction`

## Authentication

The method uses `Auth::id()` to automatically set the user_id field when creating the student record.
