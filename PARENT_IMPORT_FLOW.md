# Parent Import Flow Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     PARENT IMPORT SYSTEM                        │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   Client     │
│  (Frontend)  │
└──────┬───────┘
       │
       │ 1. Upload CSV/Excel
       ▼
┌──────────────────────────────┐
│   POST /api/main/parent/     │
│          import              │
│                              │
│  ParentController@import     │
└──────────┬───────────────────┘
           │
           │ 2. Validate file
           ▼
┌──────────────────────────────┐
│   ParentsImport Class        │
│   (Laravel Excel)            │
└──────────┬───────────────────┘
           │
           │ 3. Process each row
           ▼
    ┌──────────────┐
    │  Row Data    │
    └──────┬───────┘
           │
           ├─── 4a. Validate fields
           │
           ├─── 4b. Check NIK duplicate
           │
           ├─── 4c. Check KK duplicate
           │
           └─── 4d. Process valid row
                    │
                    ▼
            ┌───────────────────┐
            │ DB Transaction    │
            │   BEGIN           │
            └────────┬──────────┘
                     │
                     ├─── 5a. Create User
                     │        ├─ email: NIK
                     │        ├─ password: "password"
                     │        └─ name: first_name + last_name
                     │
                     ├─── 5b. Assign Role "user"
                     │
                     ├─── 5c. Create ParentProfile
                     │        └─ Link to user_id
                     │
                     ├─── 5d. COMMIT if success
                     │
                     └─── 5e. ROLLBACK if error
                              │
                              ▼
                    ┌──────────────────┐
                    │  Track Result    │
                    │  - Success count │
                    │  - Failure count │
                    │  - Error messages│
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │ Return Response  │
                    │  - Statistics    │
                    │  - Errors        │
                    │  - Info message  │
                    └──────────────────┘
```

## Data Flow Detail

```
CSV ROW:
nik,kk,first_name,last_name,gender,parent_as,...
1234567890123456,1234567890123456,Ahmad,Santoso,L,ayah,...

                    ↓ IMPORT ↓

DATABASE CHANGES:

1. USERS TABLE:
   ┌────────────────────────────────────────┐
   │ id: auto                               │
   │ name: "Ahmad Santoso"                  │
   │ email: "1234567890123456"  ← NIK!     │
   │ password: bcrypt("password")           │
   │ created_at: now()                      │
   └────────────────────────────────────────┘

2. MODEL_HAS_ROLES TABLE:
   ┌────────────────────────────────────────┐
   │ role_id: [user role id]                │
   │ model_type: "App\Models\User"          │
   │ model_id: [user.id]                    │
   └────────────────────────────────────────┘

3. PARANT_PROFILES TABLE:
   ┌────────────────────────────────────────┐
   │ id: auto                               │
   │ user_id: [user.id]         ← LINK!    │
   │ nik: "1234567890123456"                │
   │ kk: "1234567890123456"                 │
   │ first_name: "Ahmad"                    │
   │ last_name: "Santoso"                   │
   │ gender: "L"                            │
   │ parent_as: "ayah"                      │
   │ ... other fields ...                   │
   └────────────────────────────────────────┘

                    ↓ RESULT ↓

PARENT CAN LOGIN WITH:
Email: 1234567890123456
Password: password
```

## Import Process Steps

```
Step 1: PREPARE
├─ Upload CSV/Excel file
├─ Validate file format
└─ Check file size (max 10MB)

Step 2: READ & VALIDATE
├─ Read file with headers
├─ Process in chunks (50 rows)
├─ Validate each row:
│  ├─ Required fields present?
│  ├─ Valid data types?
│  ├─ Foreign keys exist?
│  └─ NIK/KK unique?

Step 3: IMPORT EACH ROW
├─ Check duplicates in DB
├─ Start transaction
├─ Create User account
│  ├─ Email = NIK
│  ├─ Password = "password"
│  └─ Name = first_name + last_name
├─ Assign "user" role
├─ Create ParentProfile
├─ Link profile to user
├─ Commit transaction
└─ Track success/failure

Step 4: REPORT RESULTS
├─ Count successes
├─ Count failures
├─ Collect error messages
└─ Return JSON response
```

## Error Handling Flow

```
┌─────────────────┐
│  Import Row     │
└────────┬────────┘
         │
    ┌────▼─────────────────────┐
    │  NIK exists?             │
    └────┬────────────┬────────┘
         │ YES        │ NO
         │            │
         ▼            ▼
    Skip Row    Check KK
         │            │
         │       ┌────▼─────────────────┐
         │       │  KK exists?          │
         │       └────┬────────┬────────┘
         │            │ YES    │ NO
         │            │        │
         │            ▼        ▼
         │       Skip Row  Validate
         │            │        │
         │            │   ┌────▼────────────┐
         │            │   │ Valid data?     │
         │            │   └────┬────┬───────┘
         │            │        │YES │NO
         │            │        │    │
         │            │        ▼    ▼
         │            │    Process Skip
         │            │        │    │
         └────────────┴────────┴────┘
                      │
                 Track Error
                      │
                      ▼
              Continue Next Row
```

## Security Consideration

```
⚠️  SECURITY ALERT ⚠️

ALL IMPORTED PARENTS HAVE:
Email: Their NIK
Password: "password"

┌─────────────────────────────────────┐
│  RECOMMENDED SECURITY MEASURES:     │
├─────────────────────────────────────┤
│ 1. Force password change on login  │
│ 2. Send credentials securely        │
│ 3. Implement email verification     │
│ 4. Set password expiry              │
│ 5. Enable 2FA if possible           │
│ 6. Log all login attempts           │
└─────────────────────────────────────┘
```

## Response Structure

```json
SUCCESS RESPONSE:
{
  "success": true,
  "message": "Import completed",
  "data": {
    "success_count": 10,      // Successfully imported
    "failure_count": 2,       // Failed to import
    "total": 12,              // Total rows processed
    "info": "User accounts created with NIK as email and default password: \"password\"",
    "errors": [               // If any failures
      "NIK 123... already exists - skipped",
      "Row 5: Gender must be L or P"
    ],
    "total_errors": 2
  }
}

ERROR RESPONSE:
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "file": ["The file must be a file of type: xlsx, xls, csv."]
  }
}
```
