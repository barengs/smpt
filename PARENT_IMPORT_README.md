# Parent Import Feature - Quick Start

## Installation Complete ✅

The parent import feature has been successfully implemented with automatic user account creation.

### Files Created/Modified:

1. **ParentsImport Class** (`app/Imports/ParentsImport.php`)

    - Handles Excel/CSV file processing
    - Creates user account for each parent
    - Uses NIK as email (username)
    - Sets default password to "password"
    - Assigns "user" role automatically
    - Validates data before import
    - Batch processing for performance
    - Error tracking and reporting

2. **ParentController** (`app/Http/Controllers/Api/Main/ParentController.php`)

    - Added `import()` method for file upload
    - Added `downloadTemplate()` method for CSV template download

3. **Routes** (`routes/api.php`)

    - `POST /api/main/parent/import` - Import parents
    - `GET /api/main/parent/import/template` - Download template

4. **Documentation & Samples**
    - `PARENT_IMPORT_GUIDE.md` - Complete guide
    - `public/csv/parent_import_sample.csv` - Sample file with 4 parent records

## Quick Test:

### 1. Download Template:

```bash
curl -X GET http://localhost:8000/api/main/parent/import/template \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o parent_template.csv
```

### 2. Import Parents:

```bash
curl -X POST http://localhost:8000/api/main/parent/import \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/Users/ROFI/Develop/proyek/smp/public/csv/parent_import_sample.csv"
```

## API Endpoints:

| Method | Endpoint                           | Description                       |
| ------ | ---------------------------------- | --------------------------------- |
| POST   | `/api/main/parent/import`          | Import parent data from Excel/CSV |
| GET    | `/api/main/parent/import/template` | Download CSV template             |

## Required CSV Columns:

**Required:**

-   `nik` - National ID (16 chars, unique)
-   `kk` - Family Card (16 chars)
-   `first_name` - First name
-   `gender` - L (Male) or P (Female)
-   `parent_as` - ayah (father) or ibu (mother)

**Optional:**

-   `last_name`, `card_address`, `domicile_address`, `phone`, `email`, `occupation_id`, `education_id`

## 🔐 User Account Creation:

For **EACH** imported parent, the system automatically:

1. ✅ Creates a user account
2. ✅ Uses **NIK as email/username**
3. ✅ Sets password to **"password"** (default)
4. ✅ Assigns **"user"** role
5. ✅ Links user to parent profile

### Login Credentials After Import:

```
Email: [NIK value from CSV]
Password: password
```

### Example:

If NIK is `1234567890123456`, then:

-   Email/Username: `1234567890123456`
-   Password: `password`

## Features:

✅ Support for Excel (.xlsx, .xls) and CSV files  
✅ Automatic user account creation  
✅ NIK used as email/username  
✅ Default password: "password"  
✅ Auto role assignment ("user")  
✅ Batch processing (50 records per batch)  
✅ Validation before import  
✅ Duplicate detection (NIK and KK)  
✅ Detailed error reporting  
✅ Template download  
✅ Foreign key validation  
✅ Transaction safety (rollback on error)  
✅ Skip on error (continues importing valid records)

## Response Format:

### Success:

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

### With Errors:

```json
{
    "success": true,
    "message": "Import completed with some errors",
    "data": {
        "success_count": 3,
        "failure_count": 1,
        "total": 4,
        "info": "User accounts created with NIK as email and default password: \"password\"",
        "errors": [
            "NIK 1234567890123456 already exists - skipped",
            "KK 1234567890123456 already exists - skipped"
        ],
        "total_errors": 2
    }
}
```

## ⚠️ Important Security Notes:

1. **All parents get the same default password: "password"**
2. **Recommended actions after import:**
    - Implement forced password change on first login
    - Send login credentials to parents securely
    - Set up password reset functionality
    - Consider email/SMS notification system

## Sample Data Structure:

The sample CSV includes 2 families (4 parents):

**Family 1 (KK: 1234567890123456):**

-   Ahmad Santoso (Father, NIK: 1234567890123456)
-   Siti Rahayu (Mother, NIK: 1234567890123457)

**Family 2 (KK: 2234567890123458):**

-   Budi Prasetyo (Father, NIK: 2234567890123458)
-   Ani Lestari (Mother, NIK: 2234567890123459)

## Testing the Import:

### Step 1: Check existing parents

```bash
curl -X GET http://localhost:8000/api/main/parent \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Step 2: Import sample data

```bash
curl -X POST http://localhost:8000/api/main/parent/import \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@public/csv/parent_import_sample.csv"
```

### Step 3: Verify user accounts created

Login with:

-   Email: `1234567890123456`
-   Password: `password`

## Next Steps:

1. ✅ Test the import with the sample CSV file
2. ⚠️ Implement password change requirement on first login
3. 📧 Set up email/SMS notification for parent credentials
4. 🔐 Configure password policy
5. 📝 Customize validation rules if needed
6. 🎯 Add authentication/authorization middleware if required
7. 📊 Monitor import performance with large files

## Database Verification:

After import, verify the data:

```sql
-- Check imported parents
SELECT pp.*, u.email, u.name
FROM parant_profiles pp
JOIN users u ON pp.user_id = u.id
WHERE pp.nik IN ('1234567890123456', '1234567890123457', '2234567890123458', '2234567890123459');

-- Check user roles
SELECT u.email, r.name as role
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email IN ('1234567890123456', '1234567890123457', '2234567890123458', '2234567890123459');
```

For detailed documentation, see: `PARENT_IMPORT_GUIDE.md`
