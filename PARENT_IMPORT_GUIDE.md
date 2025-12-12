# Parent Import Feature Guide

## Overview

The Parent Import feature allows bulk import of parent/guardian data via Excel or CSV files. Each imported parent automatically gets a user account created with their NIK as the email and "password" as the default password.

## Endpoints

### 1. Import Parents

**Endpoint:** `POST /api/main/parent/import`

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
        "success_count": 4,
        "failure_count": 0,
        "total": 4,
        "info": "User accounts created with NIK as email and default password: \"password\""
    }
}
```

**Response Example (With Errors):**

```json
{
    "success": true,
    "message": "Import completed with some errors",
    "data": {
        "success_count": 3,
        "failure_count": 1,
        "total": 4,
        "info": "User accounts created with NIK as email and default password: \"password\"",
        "errors": ["NIK 1234567890123456 already exists - skipped"],
        "total_errors": 1
    }
}
```

### 2. Download Import Template

**Endpoint:** `GET /api/main/parent/import/template`

**Headers:**

-   `Authorization: Bearer {token}`

**Response:**

-   CSV file download with headers and sample data

## File Format

### Required Columns:

1. **nik** (required) - National Identification Number (max 16 chars, unique)
2. **kk** (required) - Family Card Number (max 16 chars)
3. **first_name** (required) - Parent's first name (max 255 chars)
4. **gender** (required) - Gender: 'L' for Male or 'P' for Female
5. **parent_as** (required) - Parent type: 'ayah' (father) or 'ibu' (mother)

### Optional Columns:

6. **last_name** - Parent's last name (max 255 chars)
7. **card_address** - Address on ID card (max 255 chars)
8. **domicile_address** - Current residential address (max 255 chars)
9. **phone** - Phone number (max 15 chars)
10. **email** - Email address (valid email format, max 255 chars)
11. **occupation_id** - Occupation ID (must exist in occupations table)
12. **education_id** - Education level ID (must exist in educations table)

## CSV Template Example:

```csv
nik,kk,first_name,last_name,gender,parent_as,card_address,domicile_address,phone,email,occupation_id,education_id
1234567890123456,1234567890123456,Ahmad,Santoso,L,ayah,Jl. Merdeka No. 123 Jakarta,Jl. Sudirman No. 456 Jakarta,081234567890,ahmad@example.com,1,1
1234567890123457,1234567890123456,Siti,Rahayu,P,ibu,Jl. Merdeka No. 123 Jakarta,Jl. Sudirman No. 456 Jakarta,081234567891,siti@example.com,2,2
```

## User Account Creation

For each imported parent:

-   **Username/Email:** Uses the NIK value
-   **Password:** Default is "password" (parents should change this after first login)
-   **Role:** Automatically assigned "user" role
-   **User Name:** Combination of first_name and last_name

## Validation Rules:

1. **nik**: Required, unique, maximum 16 characters
2. **kk**: Required, unique, maximum 16 characters
3. **first_name**: Required, maximum 255 characters
4. **gender**: Required, must be 'L' or 'P' (case insensitive)
5. **parent_as**: Required, must be 'ayah' or 'ibu' (case insensitive)
6. **email**: Optional, must be valid email format
7. **occupation_id**: Optional, must exist in occupations table if provided
8. **education_id**: Optional, must exist in educations table if provided
9. **phone**: Optional, maximum 15 characters

## Import Behavior:

-   **Duplicate NIK**: If a parent with the same NIK already exists, that row will be skipped
-   **Duplicate KK**: If a parent with the same KK already exists, that row will be skipped
-   **Batch Processing**: Data is imported in batches of 50 records for optimal performance
-   **Transaction Safety**: Each parent import (user + profile) is wrapped in a transaction
-   **Error Handling**: Import continues even if some rows fail; all errors are reported
-   **User Role Assignment**: Each imported parent is automatically assigned the "user" role

## Example Usage with cURL:

### Import Parents:

```bash
curl -X POST http://localhost:8000/api/main/parent/import \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@parents.csv"
```

### Download Template:

```bash
curl -X GET http://localhost:8000/api/main/parent/import/template \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o parent_template.csv
```

## Example Usage with JavaScript (Axios):

### Import Parents:

```javascript
const formData = new FormData();
formData.append("file", fileInput.files[0]);

axios
    .post("/api/main/parent/import", formData, {
        headers: {
            "Content-Type": "multipart/form-data",
            Authorization: `Bearer ${token}`,
        },
    })
    .then((response) => {
        console.log("Import success:", response.data);
        console.log("User accounts created with NIK as email");
    })
    .catch((error) => {
        console.error("Import error:", error.response.data);
    });
```

### Download Template:

```javascript
axios
    .get("/api/main/parent/import/template", {
        headers: {
            Authorization: `Bearer ${token}`,
        },
        responseType: "blob",
    })
    .then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "parent_import_template.csv");
        document.body.appendChild(link);
        link.click();
    });
```

## Error Messages:

-   `"Validasi gagal"` - Validation failed (file format incorrect or missing)
-   `"NIK {nik} already exists - skipped"` - Duplicate NIK in database
-   `"KK {kk} already exists - skipped"` - Duplicate Family Card in database
-   `"Row {row}: {errors}"` - Validation errors for specific row
-   `"Gender must be L or P"` - Invalid gender value
-   `"Parent type must be ayah or ibu"` - Invalid parent_as value
-   `"Occupation ID does not exist"` - Referenced occupation_id not found
-   `"Education ID does not exist"` - Referenced education_id not found

## Security Notes:

⚠️ **Important Security Information:**

1. All imported parents will have the same default password: "password"
2. It is **HIGHLY RECOMMENDED** to:
    - Force password change on first login
    - Notify parents about their accounts via email/SMS
    - Implement a password reset mechanism
    - Consider generating random passwords and sending them securely

## Best Practices:

1. **Test with Small Batch**: Start with a small CSV file to test the import
2. **Validate Data**: Ensure all required fields are filled and valid
3. **Check Foreign Keys**: Verify that occupation_id and education_id exist before importing
4. **Unique NIK**: Ensure each NIK is unique in your CSV file
5. **Unique KK**: Family members can share the same KK, but each KK should be unique per parent profile
6. **Use Template**: Download and use the provided template for consistency
7. **Monitor Errors**: Review error messages to fix data issues
8. **Backup Database**: Always backup your database before large imports
9. **Security**: Plan how to notify parents about their login credentials

## Troubleshooting:

**Q: Import fails with "file must be xlsx, xls, or csv"**
A: Ensure your file has the correct extension and is a valid Excel/CSV file

**Q: All rows are being skipped**
A: Check if the NIK or KK values already exist in the database

**Q: "Occupation ID does not exist" error**
A: Verify that the occupation_id values match existing occupations in your database

**Q: "Education ID does not exist" error**
A: Verify that the education_id values match existing education levels in your database

**Q: Import is slow**
A: The import processes 50 records at a time. For very large files (>500 records), consider splitting into smaller batches

**Q: How to handle password distribution?**
A: After import, you should:

-   Export a list of NIK and default passwords
-   Send credentials to parents securely (email, SMS, or printed notice)
-   Implement forced password change on first login

## Post-Import Actions:

After successfully importing parents:

1. **Verify User Accounts**: Check that user accounts were created

    ```sql
    SELECT u.*, pp.nik, pp.first_name
    FROM users u
    JOIN parant_profiles pp ON u.id = pp.user_id
    ```

2. **Test Login**: Try logging in with NIK and password "password"

3. **Notify Parents**: Send login credentials to parents

4. **Configure Password Policy**: Set up password change requirements

5. **Review Errors**: Check the error list and fix any skipped records
