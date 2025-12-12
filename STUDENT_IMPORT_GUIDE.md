# Student Import Feature Guide

## Overview

The Student Import feature allows bulk import of student data via Excel or CSV files.

## Endpoints

### 1. Import Students

**Endpoint:** `POST /api/student/import`

**Headers:**

-   `Content-Type: multipart/form-data`
-   `Authorization: Bearer {token}`

**Request Body:**

-   `file` (required): Excel (.xlsx, .xls) or CSV file (max 10MB)

**Response Example (Success):**

```json
{
    "success": true,
    "message": "Import completed",
    "data": {
        "success_count": 45,
        "failure_count": 5,
        "total": 50
    }
}
```

**Response Example (With Errors):**

```json
{
    "success": true,
    "message": "Import completed with some errors",
    "data": {
        "success_count": 45,
        "failure_count": 5,
        "total": 50,
        "errors": [
            "NIS 2024001 already exists - skipped",
            "Row 10: Gender must be L or P"
        ],
        "total_errors": 5
    }
}
```

### 2. Download Import Template

**Endpoint:** `GET /api/student/import/template`

**Headers:**

-   `Authorization: Bearer {token}`

**Response:**

-   CSV file download with headers and sample data

## File Format

### Required Columns:

1. **nis** (required) - Student Identification Number (max 20 chars)
2. **first_name** (required) - Student's first name (max 255 chars)
3. **gender** (required) - Gender: 'L' for Male or 'P' for Female
4. **program_id** (required) - Program ID (must exist in programs table)

### Optional Columns:

5. **last_name** - Student's last name (max 255 chars)
6. **parent_id** - Parent's NIK or identifier
7. **period** - Academic period (max 10 chars)
8. **nik** - National Identification Number (max 16 chars)
9. **kk** - Family Card Number (max 16 chars)
10. **address** - Student's address
11. **born_in** - Place of birth (max 255 chars)
12. **born_at** - Date of birth (format: YYYY-MM-DD or DD/MM/YYYY)
13. **last_education** - Previous education level (max 255 chars)
14. **village_id** - Village ID (must exist in villages table)
15. **village** - Village name (max 255 chars)
16. **district** - District name (max 255 chars)
17. **postal_code** - Postal code (max 10 chars)
18. **phone** - Phone number (max 15 chars)
19. **hostel_id** - Hostel ID (must exist in hostels table)
20. **status** - Status: 'Tidak Aktif', 'Aktif', 'Tugas', 'Lulus', or 'Dikeluarkan' (default: 'Aktif')

## CSV Template Example:

```csv
nis,first_name,last_name,gender,program_id,parent_id,period,nik,kk,address,born_in,born_at,last_education,village_id,village,district,postal_code,phone,hostel_id,status
2024001,John,Doe,L,1,NIK001,2024,1234567890123456,1234567890123456,Jl. Contoh No. 123,Jakarta,2005-01-15,SMP,1,Desa Contoh,Kec. Contoh,12345,081234567890,1,Aktif
2024002,Jane,Smith,P,1,NIK002,2024,1234567890123457,1234567890123457,Jl. Example No. 456,Bandung,2005-03-20,SMP,2,Desa Example,Kec. Example,12346,081234567891,2,Aktif
```

## Validation Rules:

1. **nis**: Required, unique, maximum 20 characters
2. **first_name**: Required, maximum 255 characters
3. **gender**: Required, must be 'L' or 'P' (case insensitive)
4. **program_id**: Required, must exist in programs table
5. **nik**: Optional, maximum 16 characters, unique
6. **status**: Optional, must be one of: 'Tidak Aktif', 'Aktif', 'Tugas', 'Lulus', 'Dikeluarkan'
7. **hostel_id**: Optional, must exist in hostels table if provided
8. **parent_id**: Optional, string identifier
9. **born_at**: Optional, valid date format

## Import Behavior:

-   **Duplicate NIS**: If a student with the same NIS already exists, that row will be skipped
-   **Batch Processing**: Data is imported in batches of 100 records for optimal performance
-   **Error Handling**: Import continues even if some rows fail; all errors are reported
-   **Date Handling**: Supports multiple date formats including Excel date serial numbers
-   **Case Insensitive Gender**: Both 'L'/'l' and 'P'/'p' are accepted for gender

## Example Usage with cURL:

### Import Students:

```bash
curl -X POST http://localhost:8000/api/student/import \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@students.csv"
```

### Download Template:

```bash
curl -X GET http://localhost:8000/api/student/import/template \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o student_template.csv
```

## Example Usage with JavaScript (Axios):

### Import Students:

```javascript
const formData = new FormData();
formData.append("file", fileInput.files[0]);

axios
    .post("/api/student/import", formData, {
        headers: {
            "Content-Type": "multipart/form-data",
            Authorization: `Bearer ${token}`,
        },
    })
    .then((response) => {
        console.log("Import success:", response.data);
    })
    .catch((error) => {
        console.error("Import error:", error.response.data);
    });
```

### Download Template:

```javascript
axios
    .get("/api/student/import/template", {
        headers: {
            Authorization: `Bearer ${token}`,
        },
        responseType: "blob",
    })
    .then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "student_import_template.csv");
        document.body.appendChild(link);
        link.click();
    });
```

## Error Messages:

-   `"Validasi gagal"` - Validation failed (file format incorrect or missing)
-   `"NIS {nis} already exists - skipped"` - Duplicate NIS in database
-   `"Row {row}: {errors}"` - Validation errors for specific row
-   `"Gender must be L or P"` - Invalid gender value
-   `"Program ID does not exist"` - Referenced program_id not found
-   `"Hostel ID does not exist"` - Referenced hostel_id not found

## Best Practices:

1. **Test with Small Batch**: Start with a small CSV file to test the import
2. **Validate Data**: Ensure all required fields are filled and valid
3. **Check Foreign Keys**: Verify that program_id and hostel_id exist before importing
4. **Use Template**: Download and use the provided template for consistency
5. **Monitor Errors**: Review error messages to fix data issues
6. **Backup Database**: Always backup your database before large imports

## Troubleshooting:

**Q: Import fails with "file must be xlsx, xls, or csv"**
A: Ensure your file has the correct extension and is a valid Excel/CSV file

**Q: All rows are being skipped**
A: Check if the NIS values already exist in the database

**Q: "Program ID does not exist" error**
A: Verify that the program_id values match existing programs in your database

**Q: Date format errors**
A: Use YYYY-MM-DD format or DD/MM/YYYY format for dates

**Q: Import is slow**
A: The import processes 100 records at a time. For very large files (>1000 records), consider splitting into smaller batches
