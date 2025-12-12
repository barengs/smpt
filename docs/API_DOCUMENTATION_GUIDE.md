# API Documentation Guide

## Accessing the API Documentation

The Education Management System API documentation is automatically generated using **Scramble** and provides an interactive, comprehensive interface for exploring all available endpoints.

### 📍 Access URL

```
http://your-domain.com/docs/api
```

**Local Development:**

```
http://localhost:8000/docs/api
```

---

## What You'll Find

### 🏠 Home Page

When you access `/docs/api`, you'll see:

1. **API Overview**

    - Welcome message and introduction
    - Authentication instructions
    - Main features summary
    - Response format guidelines

2. **Main Features Section**

    - 📚 Student Management
    - 👥 Parent Management
    - 👨‍🏫 Staff Management
    - 📅 Academic Operations
    - 🏢 Internship Management
    - 📊 Reports & Analytics

3. **Import Features**

    - Student Import details
    - Parent Import with auto user creation
    - Template download instructions

4. **Technical Information**
    - Authentication (JWT)
    - Response formats
    - Rate limiting
    - File upload specifications
    - Error codes

---

## Endpoint Organization

All endpoints are organized by **tags** for easy navigation:

### 📚 Student Management

-   GET `/api/main/student` - List all students
-   POST `/api/main/student` - Create new student
-   GET `/api/main/student/{id}` - Get student details
-   PUT `/api/main/student/{id}` - Update student
-   DELETE `/api/main/student/{id}` - Delete student
-   **POST `/api/main/student/import`** - Batch import from Excel/CSV
-   **GET `/api/main/student/import/template`** - Download import template

### 👥 Parent Management

-   GET `/api/main/parent` - List all parents
-   POST `/api/main/parent` - Create new parent
-   GET `/api/main/parent/{id}` - Get parent details
-   PUT `/api/main/parent/{id}` - Update parent
-   DELETE `/api/main/parent/{id}` - Delete parent
-   **POST `/api/main/parent/import`** - Batch import with auto user creation
-   **GET `/api/main/parent/import/template`** - Download import template

---

## Using the Documentation

### 1. Browse Endpoints

-   Click on any **tag** in the left sidebar to expand the group
-   Click on an **endpoint** to view its details

### 2. View Endpoint Details

Each endpoint shows:

-   **Description:** What the endpoint does
-   **Parameters:** Required and optional parameters with types and examples
-   **Request Body:** Expected data structure for POST/PUT requests
-   **Response Examples:** Multiple scenarios (success, errors)
-   **Authentication:** Whether JWT token is required

### 3. Try It Out! 🚀

Scramble includes an interactive "Try It" feature:

1. **Authenticate First:**

    - Go to `POST /api/auth/login`
    - Click "Try It"
    - Enter your credentials
    - Copy the JWT token from response

2. **Set Authorization:**

    - At the top of the page, find the "Authorization" section
    - Paste your JWT token
    - Format: `Bearer {your-token}`

3. **Test Endpoints:**
    - Navigate to any endpoint
    - Click "Try It"
    - Fill in parameters/body
    - Click "Send"
    - View live response!

---

## Import Endpoints Documentation

### Student Import

**GET `/api/main/student/import/template`**

Downloads a pre-formatted Excel template (.xlsx) with:

-   All column headers (required and optional)
-   One sample row with example data
-   Bold headers and sized columns
-   Ready to fill and import

**Response:**

-   File download: `student_import_template.xlsx`
-   Opens directly in Excel

---

**POST `/api/main/student/import`**

Upload filled Excel/CSV file to batch import student data.

**Key Features:**

-   Validates data and checks duplicate NIS
-   Handles numeric fields correctly (NIK, KK, phone)
-   Auto-records staff member who performed import
-   Processes 100 records per batch
-   Non-blocking (continues on errors)
-   Detailed error reports

**Request:**

-   Method: POST
-   Content-Type: multipart/form-data
-   Body: `file` (Excel/CSV file, max 10MB)

**Response Example (Success):**

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

---

### Parent Import

**GET `/api/main/parent/import/template`**

Downloads Excel template for parent import.

**Special Notes:**

-   NIK will be used as email/username
-   Default password: "password"
-   Auto-assigns "user" role

---

**POST `/api/main/parent/import`**

Upload filled Excel/CSV file to batch import parent data with automatic user account creation.

**Auto User Creation:**

-   Creates user account for each parent
-   Email/Username: NIK from Excel
-   Password: "password" (should force change on first login)
-   Role: "user"
-   Transaction safety (rollback on error)

**Response Example (Success):**

```json
{
    "success": true,
    "message": "Import completed",
    "data": {
        "success_count": 45,
        "failure_count": 5,
        "total": 50,
        "info": "User accounts created with NIK as email and default password: \"password\"",
        "errors": ["Row 3: NIK already exists - skipped"],
        "total_errors": 5
    }
}
```

---

## Response Scenarios

Each endpoint in Scramble documentation shows multiple response scenarios:

### Success Responses

-   **200** - Success (GET, PUT, DELETE)
-   **201** - Created (POST)

### Error Responses

-   **400** - Bad Request
-   **401** - Unauthorized (missing/invalid token)
-   **403** - Forbidden (insufficient permissions)
-   **404** - Not Found
-   **422** - Validation Error
-   **500** - Server Error

Click on each scenario to see the exact response structure!

---

## Authentication Guide

### Step 1: Login

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "your-email@example.com",
  "password": "your-password"
}
```

**Response:**

```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

### Step 2: Use Token

For all subsequent requests, include the token:

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci...
```

In Scramble "Try It":

1. Copy the token
2. Find "Authorization" at top
3. Paste: `Bearer {token}`
4. All requests now authenticated!

---

## Tips for Frontend Developers

### 1. Explore First

-   Browse all endpoints in Scramble
-   Read descriptions and parameter requirements
-   Check response examples

### 2. Test in Scramble

-   Use "Try It" to test endpoints live
-   Verify request/response formats
-   Understand error scenarios

### 3. Copy Examples

-   Scramble shows exact request formats
-   Copy response structures for TypeScript interfaces
-   Use error examples for error handling

### 4. Check Import Details

-   Download templates to see exact column names
-   Test import with sample data
-   Review error messages for validation rules

---

## Common Import Errors

All documented in Scramble responses!

### Student Import

**Error: "NIS already exists"**

-   Cause: Duplicate student ID
-   Solution: Check existing students, use unique NIS

**Error: "The gender field must be L or P"**

-   Cause: Invalid gender value
-   Solution: Use only 'L' (Male) or 'P' (Female)

**Error: "The program_id field is required"**

-   Cause: Missing program ID
-   Solution: Fill program_id column

### Parent Import

**Error: "NIK already exists"**

-   Cause: Duplicate National ID
-   Solution: Check existing parents

**Error: "User account creation failed"**

-   Cause: Database transaction error
-   Solution: Check database connection, retry import

---

## Technical Details

### Request Formats

**JSON Request:**

```http
Content-Type: application/json

{
  "field": "value"
}
```

**File Upload:**

```http
Content-Type: multipart/form-data

file: [binary file data]
```

### Response Format

All responses follow this structure:

```json
{
  "success": true|false,
  "message": "Human-readable message",
  "data": { ... },
  "errors": { ... }
}
```

---

## Rate Limiting

-   **Authenticated:** 60 requests/minute
-   **Unauthenticated:** 10 requests/minute

Check response headers:

-   `X-RateLimit-Limit`
-   `X-RateLimit-Remaining`

---

## Additional Resources

### In Scramble Documentation:

-   Complete endpoint list
-   Interactive testing
-   Request/response examples
-   Parameter specifications

### In Project Files:

-   `docs/IMPORT_FEATURES_DOCUMENTATION.md` - Detailed import guide
-   `README.md` - Project overview

---

## Support

**Questions about API?**

1. Check Scramble documentation first
2. Review response examples
3. Test with "Try It" feature
4. Refer to `docs/` folder for detailed guides

---

## Summary

✅ **Access:** `http://your-domain.com/docs/api`  
✅ **Authentication:** JWT Bearer token  
✅ **Interactive:** Try It feature available  
✅ **Organized:** Grouped by feature tags  
✅ **Complete:** All endpoints documented  
✅ **Examples:** Request/response samples included

**Everything frontend developers need is in the Scramble documentation!** 🚀
