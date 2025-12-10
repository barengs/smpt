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
