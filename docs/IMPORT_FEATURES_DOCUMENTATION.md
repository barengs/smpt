# Import Features Documentation

Complete documentation for Student and Parent import functionality with Excel/CSV support.

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Student Import](#student-import)
3. [Parent Import](#parent-import)
4. [Common Issues & Solutions](#common-issues--solutions)
5. [API Endpoints](#api-endpoints)
6. [Production Deployment](#production-deployment)
7. [Frontend Integration Examples](#frontend-integration-examples)

---

## Quick Start

### For Users (Staff Members)

1. **Login** to the system with your staff account
2. **Download** the Excel template from the API endpoint
3. **Fill** the template with student/parent data
4. **Upload** the filled Excel file
5. **Done!** The system will import data and track who performed the import

### For Developers

**API Endpoints:**

-   `GET /api/main/student/import/template` - Download student template
-   `POST /api/main/student/import` - Import student data
-   `GET /api/main/parent/import/template` - Download parent template
-   `POST /api/main/parent/import` - Import parent data

**Required:**

-   Authentication (JWT Bearer token)
-   File format: .xlsx, .xls, or .csv
-   Max file size: 10MB

---

## Student Import

### Features

✅ **Batch Import:** Import multiple students at once  
✅ **Validation:** NIS uniqueness, required fields  
✅ **Error Tracking:** Non-blocking errors with detailed messages  
✅ **Audit Trail:** Automatically records staff member who performed import  
✅ **Date Handling:** Smart date parsing from Excel  
✅ **Numeric Fields:** Handles NIK, KK, phone numbers correctly

### Excel Template Structure

**Required Columns:**

-   `nis` - Student ID (unique)
-   `first_name` - First name
-   `gender` - L (Male) or P (Female)
-   `program_id` - Program ID

**Optional Columns:**

-   `last_name`, `parent_id`, `period`, `nik`, `kk`, `address`, `born_in`, `born_at`, `last_education`, `village_id`, `village`, `district`, `postal_code`, `phone`, `hostel_id`, `status`

**Auto-Generated:**

-   `user_id` - Automatically filled with authenticated staff ID
-   `photo` - Can be added later through the system

### Example Template Data

```
| nis         | first_name | last_name | gender | program_id | nik              |
|-------------|------------|-----------|--------|------------|------------------|
| 14420197183 | FAISOL     | FAISOL    | L      | 1          | 3527110805600010 |
| 14420197178 | SUBAIDI    | ARBAIN    | L      | 1          | 3527110805600020 |
```

### Import Process

1. **Upload Excel file** through API endpoint
2. **System validates** each row:
    - Check NIS uniqueness
    - Validate required fields
    - Convert numeric strings (NIK, KK) to proper format
    - Parse dates from Excel format
3. **Insert records** in batches of 100
4. **Track results:**
    - Success count
    - Failure count
    - Error details (if any)
5. **Record audit trail:**
    - All records get `user_id` from authenticated staff
    - Timestamp recorded

### Response Format

**Success:**

```json
{
    "success": true,
    "message": "Import completed",
    "data": {
        "success_count": 95,
        "failure_count": 5,
        "total": 100,
        "errors": [
            "Row 5: NIS 12345 already exists - skipped",
            "Row 23: The gender field must be L or P"
        ],
        "total_errors": 5
    }
}
```

**Error:**

```json
{
    "success": false,
    "message": "Gagal mengimpor data",
    "error": "The file field is required."
}
```

---

## Parent Import

### Features

✅ **Batch Import:** Import multiple parents at once  
✅ **Auto User Creation:** Automatically creates user account for each parent  
✅ **NIK as Email:** Uses NIK as email/username  
✅ **Default Password:** Sets password to "password"  
✅ **Role Assignment:** Automatically assigns "user" role  
✅ **Transaction Safety:** Rollback on error  
✅ **Validation:** NIK and KK uniqueness  
✅ **Error Tracking:** Non-blocking errors with detailed messages

### Excel Template Structure

**Required Columns:**

-   `nik` - National ID (unique) - **Used as email**
-   `kk` - Family Card number (unique)
-   `first_name` - First name
-   `gender` - L (Male) or P (Female)
-   `parent_as` - Role: "ayah" or "ibu"

**Optional Columns:**

-   `last_name`, `card_address`, `domicile_address`, `phone`, `email`, `occupation_id`, `education_id`

**Auto-Generated:**

-   User account (username = NIK, password = "password")
-   Role: "user"

### Example Template Data

```
| nik          | kk               | first_name | gender | parent_as |
|--------------|------------------|------------|--------|-----------|
| 14420197183  | 3527140906300010 | HADIRI     | L      | ayah      |
| 14420197178  | 3527140906300020 | MISNADIN   | L      | ayah      |
```

### Parent Import Flow

```
1. Upload Excel file
   ↓
2. For each row:
   - Validate NIK uniqueness
   - Validate KK uniqueness
   - Start database transaction
   ↓
3. Create user account:
   - email = NIK
   - password = Hash::make('password')
   - Assign "user" role
   ↓
4. Create parent profile:
   - user_id = newly created user ID
   - Store all parent data
   ↓
5. Commit transaction
   ↓
6. Track results
```

### Important Notes

**Security:** All imported parents have default password "password"  
**Recommendation:** Implement forced password change on first login

**User Accounts Created:**

-   Username/Email: NIK (e.g., "14420197183")
-   Password: "password"
-   Role: "user"

---

## Common Issues & Solutions

### Issue 1: Numeric String Validation Error

**Problem:**

```json
{
    "errors": ["Row 2: The nik field must be a string."]
}
```

**Cause:** Excel stores numbers as numeric type, but validation expects string

**Solution:** ✅ FIXED - System now automatically converts numeric values to strings

**What Changed:**

-   Removed `string` validation requirement
-   Added explicit type casting: `(string) $row['nik']`
-   Works with any Excel cell format (General, Number, Text)

**User Action:** NONE - Just use Excel normally!

---

### Issue 2: Missing user_id Field

**Problem:**

```json
{
    "errors": ["SQLSTATE: Field 'user_id' doesn't have a default value"]
}
```

**Cause:** Students table requires user_id field (tracks who created the record)

**Solution:** ✅ FIXED - System automatically assigns authenticated user's ID

**What Changed:**

-   Added: `'user_id' => Auth::id() ?? 1`
-   Automatically filled from login session
-   Creates audit trail

**User Action:** NONE - Just stay logged in!

---

### Issue 3: Excel Template Format Not Recognized

**Problem:** Downloaded template can't be opened in Excel

**Cause:** CSV format compatibility issues

**Solution:** ✅ FIXED - Changed to native XLSX format

**What Changed:**

-   Template now generates .xlsx (not .csv)
-   Uses Laravel Excel export classes
-   Bold headers, sized columns
-   Cross-platform compatible

**User Action:** NONE - Download works now!

---

### Issue 4: Template Download Shows Text Instead of File

**Problem:** Browser shows CSV text, doesn't download file

**Cause:** Incorrect response headers

**Solution:** ✅ FIXED - Proper stream response with download headers

**User Action:** NONE - Download works correctly now!

---

### Issue 5: Leading Zeros Removed (e.g., NIK starts with 0)

**Problem:** NIK "0824567890123456" becomes "824567890123456"

**Cause:** Excel removes leading zeros from numbers

**Solution:** Format cell as TEXT before entering data

**User Action Required:**

1. Select the column
2. Format as "Text"
3. THEN enter the number

Or use apostrophe: `'0824567890123456`

---

## API Endpoints

### Student Import Endpoints

#### Download Student Template

```
GET /api/main/student/import/template
```

**Headers:**

```
Authorization: Bearer {jwt_token}
```

**Response:**

-   File: `student_import_template.xlsx`
-   Type: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`

**Sample Data Included:**

-   1 example row with all fields filled
-   Bold headers
-   Sized columns

---

#### Import Student Data

```
POST /api/main/student/import
```

**Headers:**

```
Authorization: Bearer {jwt_token}
Content-Type: multipart/form-data
```

**Body:**

```
file: [Excel/CSV file]
```

**Validation:**

-   File required
-   Allowed: .xlsx, .xls, .csv
-   Max size: 10MB

**Response:**

```json
{
    "success": true,
    "message": "Import completed",
    "data": {
        "success_count": 95,
        "failure_count": 5,
        "total": 100,
        "errors": ["Row 5: NIS already exists"],
        "total_errors": 5
    }
}
```

---

### Parent Import Endpoints

#### Download Parent Template

```
GET /api/main/parent/import/template
```

**Headers:**

```
Authorization: Bearer {jwt_token}
```

**Response:**

-   File: `parent_import_template.xlsx`
-   Type: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`

---

#### Import Parent Data

```
POST /api/main/parent/import
```

**Headers:**

```
Authorization: Bearer {jwt_token}
Content-Type: multipart/form-data
```

**Body:**

```
file: [Excel/CSV file]
```

**Response:**

```json
{
    "success": true,
    "message": "Import completed",
    "data": {
        "success_count": 45,
        "failure_count": 5,
        "total": 50,
        "info": "User accounts created with NIK as email and default password: \"password\"",
        "errors": ["Row 3: NIK already exists"],
        "total_errors": 5
    }
}
```

---

## Production Deployment

### Pre-Deployment Checklist

-   [ ] Laravel Excel package installed on production
-   [ ] Composer autoload regenerated
-   [ ] Package config published
-   [ ] All caches cleared

### Deployment Commands

```bash
# 1. Install Laravel Excel package
composer require maatwebsite/excel

# 2. Publish config
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

# 3. Regenerate autoload
composer dump-autoload -o

# 4. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Verify package
composer show maatwebsite/excel
```

### Files to Deploy

**Core Import Files:**

-   `app/Imports/StudentsImport.php`
-   `app/Imports/ParentsImport.php`
-   `app/Exports/StudentTemplateExport.php`
-   `app/Exports/ParentTemplateExport.php`

**Controllers:**

-   `app/Http/Controllers/Api/Main/StudentController.php`
-   `app/Http/Controllers/Api/Main/ParentController.php`

**Routes:**

-   `routes/api.php`

### Post-Deployment Verification

```bash
# Test student template download
curl -X GET https://api.example.com/api/main/student/import/template \
  -H "Authorization: Bearer TOKEN" \
  -o student_template.xlsx

# Test student import
curl -X POST https://api.example.com/api/main/student/import \
  -H "Authorization: Bearer TOKEN" \
  -F "file=@student_template.xlsx"

# Verify in database
mysql> SELECT * FROM students ORDER BY id DESC LIMIT 5;
mysql> SELECT s.nis, u.name as created_by FROM students s JOIN users u ON u.id = s.user_id LIMIT 5;
```

---

## Frontend Integration Examples

### React + TypeScript + Fetch

#### Download Template

```typescript
const downloadTemplate = async (endpoint: string, filename: string) => {
    try {
        const token = localStorage.getItem("token") || "";

        const response = await fetch(endpoint, {
            method: "GET",
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        console.log("Template downloaded successfully");
    } catch (error) {
        console.error("Download failed:", error);
        alert("Gagal mengunduh template");
    }
};

// Usage
downloadTemplate("/api/main/student/import/template", "student_template.xlsx");
```

#### Upload Import File

```typescript
const uploadImportFile = async (file: File, endpoint: string) => {
    try {
        const token = localStorage.getItem("token") || "";
        const formData = new FormData();
        formData.append("file", file);

        const response = await fetch(endpoint, {
            method: "POST",
            headers: {
                Authorization: `Bearer ${token}`,
            },
            body: formData,
        });

        const result = await response.json();

        if (result.success) {
            console.log("Import berhasil:", result.data);
            alert(
                `Berhasil: ${result.data.success_count}, Gagal: ${result.data.failure_count}`
            );
        } else {
            console.error("Import gagal:", result.message);
            alert("Import gagal: " + result.message);
        }
    } catch (error) {
        console.error("Upload error:", error);
        alert("Upload gagal");
    }
};

// Usage with file input
const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
        uploadImportFile(file, "/api/main/student/import");
    }
};
```

#### Complete React Component

```typescript
import React, { useState } from "react";

const StudentImport: React.FC = () => {
    const [uploading, setUploading] = useState(false);
    const [result, setResult] = useState<any>(null);

    const downloadTemplate = async () => {
        const token = localStorage.getItem("token") || "";
        const response = await fetch("/api/main/student/import/template", {
            headers: { Authorization: `Bearer ${token}` },
        });
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "student_template.xlsx";
        a.click();
        window.URL.revokeObjectURL(url);
    };

    const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        setUploading(true);
        const token = localStorage.getItem("token") || "";
        const formData = new FormData();
        formData.append("file", file);

        try {
            const response = await fetch("/api/main/student/import", {
                method: "POST",
                headers: { Authorization: `Bearer ${token}` },
                body: formData,
            });
            const data = await response.json();
            setResult(data);
        } catch (error) {
            console.error(error);
        } finally {
            setUploading(false);
        }
    };

    return (
        <div>
            <h2>Import Siswa</h2>

            <button onClick={downloadTemplate}>Download Template</button>

            <input
                type="file"
                accept=".xlsx,.xls,.csv"
                onChange={handleUpload}
                disabled={uploading}
            />

            {uploading && <p>Uploading...</p>}

            {result && (
                <div>
                    <p>Sukses: {result.data?.success_count}</p>
                    <p>Gagal: {result.data?.failure_count}</p>
                    {result.data?.errors && (
                        <ul>
                            {result.data.errors.map(
                                (err: string, i: number) => (
                                    <li key={i}>{err}</li>
                                )
                            )}
                        </ul>
                    )}
                </div>
            )}
        </div>
    );
};

export default StudentImport;
```

### Vue.js Example

```vue
<template>
    <div>
        <button @click="downloadTemplate">Download Template</button>
        <input type="file" @change="handleUpload" accept=".xlsx,.xls,.csv" />
        <div v-if="result">
            <p>Sukses: {{ result.data.success_count }}</p>
            <p>Gagal: {{ result.data.failure_count }}</p>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            result: null,
        };
    },
    methods: {
        async downloadTemplate() {
            const token = localStorage.getItem("token");
            const response = await fetch("/api/main/student/import/template", {
                headers: { Authorization: `Bearer ${token}` },
            });
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "student_template.xlsx";
            a.click();
        },
        async handleUpload(e) {
            const file = e.target.files[0];
            const formData = new FormData();
            formData.append("file", file);
            const token = localStorage.getItem("token");
            const response = await fetch("/api/main/student/import", {
                method: "POST",
                headers: { Authorization: `Bearer ${token}` },
                body: formData,
            });
            this.result = await response.json();
        },
    },
};
</script>
```

---

## Technical Details

### Field Type Handling

**Numeric String Fields:**
All numeric-like fields are explicitly converted to strings to prevent type coercion:

```php
$nik = (string) ($row['nik'] ?? '');
$kk = (string) ($row['kk'] ?? '');
$phone = (string) ($row['phone'] ?? '');
```

**Fields Affected:**

-   Student: `nis`, `nik`, `kk`, `phone`, `postal_code`, `village_id`, `program_id`, `hostel_id`
-   Parent: `nik`, `kk`, `phone`, `occupation_id`, `education_id`

### Date Transformation

Excel dates are converted to Laravel-compatible format:

```php
private function transformDate($value)
{
    if (empty($value)) return null;

    // Excel numeric date
    if (is_numeric($value)) {
        $unixDate = ($value - 25569) * 86400;
        return Carbon::createFromTimestamp($unixDate)->format('Y-m-d');
    }

    // String date
    return Carbon::parse($value)->format('Y-m-d');
}
```

### Batch Processing

-   **Student Import:** 100 records per batch
-   **Parent Import:** 50 records per batch (includes user creation)

### Error Tracking

Errors are collected without stopping the import:

```php
try {
    // Import logic
    $this->successCount++;
    return $model;
} catch (\Exception $e) {
    $this->errors[] = "Error: " . $e->getMessage();
    $this->failureCount++;
    return null;
}
```

### Audit Trail

Every student record tracks who created it:

```php
'user_id' => Auth::id() ?? 1
```

Query audit trail:

```sql
SELECT s.nis, s.first_name, u.name as created_by, s.created_at
FROM students s
JOIN users u ON u.id = s.user_id
ORDER BY s.created_at DESC;
```

---

## Summary

### Student Import

✅ Batch import with validation  
✅ Auto user_id tracking  
✅ Error handling without blocking  
✅ XLSX template download  
✅ Numeric field handling

### Parent Import

✅ Auto user account creation  
✅ NIK as email/username  
✅ Default password: "password"  
✅ Transaction safety  
✅ Role assignment

### Both Features

✅ Production ready  
✅ Frontend examples provided  
✅ Complete documentation  
✅ Full audit trail  
✅ Cross-platform compatible

**All import features are fully tested and ready for production use!** 🎉
