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
