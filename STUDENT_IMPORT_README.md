# Student Import Feature - Quick Start

## Installation Complete ✅

The student import feature has been successfully implemented with the following components:

### Files Created/Modified:

1. **StudentsImport Class** (`app/Imports/StudentsImport.php`)

    - Handles Excel/CSV file processing
    - Validates data before import
    - Batch processing for performance
    - Error tracking and reporting

2. **StudentController** (`app/Http/Controllers/Api/Main/StudentController.php`)

    - Added `import()` method for file upload
    - Added `downloadTemplate()` method for CSV template download

3. **Routes** (`routes/api.php`)

    - `POST /api/main/student/import` - Import students
    - `GET /api/main/student/import/template` - Download template

4. **Documentation**

    - `STUDENT_IMPORT_GUIDE.md` - Complete guide
    - `public/csv/student_import_sample.csv` - Sample file

5. **Package Installed**
    - `maatwebsite/excel` v3.1 - Laravel Excel package

## Quick Test:

### 1. Download Template:

```bash
curl -X GET http://localhost:8000/api/main/student/import/template \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o template.csv
```

### 2. Import Students:

```bash
curl -X POST http://localhost:8000/api/main/student/import \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/Users/ROFI/Develop/proyek/smp/public/csv/student_import_sample.csv"
```

## API Endpoints:

| Method | Endpoint                            | Description                        |
| ------ | ----------------------------------- | ---------------------------------- |
| POST   | `/api/main/student/import`          | Import student data from Excel/CSV |
| GET    | `/api/main/student/import/template` | Download CSV template              |

## Required CSV Columns:

**Required:**

-   `nis` - Student ID (unique)
-   `first_name` - First name
-   `gender` - L (Male) or P (Female)
-   `program_id` - Program ID (must exist)

**Optional:**

-   `last_name`, `parent_id`, `period`, `nik`, `kk`, `address`, `born_in`, `born_at`, `last_education`, `village_id`, `village`, `district`, `postal_code`, `phone`, `hostel_id`, `status`

## Features:

✅ Support for Excel (.xlsx, .xls) and CSV files
✅ Batch processing (100 records per batch)
✅ Validation before import
✅ Duplicate detection (NIS)
✅ Detailed error reporting
✅ Template download
✅ Date format conversion
✅ Foreign key validation
✅ Skip on error (continues importing valid records)

## Response Format:

### Success:

```json
{
    "success": true,
    "message": "Import completed",
    "data": {
        "success_count": 3,
        "failure_count": 0,
        "total": 3
    }
}
```

### With Errors:

```json
{
    "success": true,
    "message": "Import completed with some errors",
    "data": {
        "success_count": 2,
        "failure_count": 1,
        "total": 3,
        "errors": ["NIS 2024001 already exists - skipped"],
        "total_errors": 1
    }
}
```

## Next Steps:

1. Test the import with the sample CSV file
2. Customize validation rules if needed
3. Add authentication/authorization middleware if required
4. Configure file size limits in `php.ini` if needed
5. Monitor import performance with large files

For detailed documentation, see: `STUDENT_IMPORT_GUIDE.md`
