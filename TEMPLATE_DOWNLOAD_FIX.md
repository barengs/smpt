# Template Download Fix - Testing Guide

## Problem Fixed ✅

The template download was showing CSV content directly in the browser instead of triggering a file download. This has been fixed by:

1. Using `response()->stream()` instead of `response()` with string content
2. Using `fputcsv()` for proper CSV formatting
3. Adding UTF-8 BOM for better Excel compatibility
4. Adding proper cache control headers
5. Setting correct Content-Type with charset

## Changes Made:

### StudentController.php - downloadTemplate()

-   ✅ Changed from string concatenation to stream response
-   ✅ Uses `fputcsv()` for proper CSV formatting
-   ✅ Adds UTF-8 BOM for Excel compatibility
-   ✅ Proper headers for file download

### ParentController.php - downloadTemplate()

-   ✅ Changed from string concatenation to stream response
-   ✅ Uses `fputcsv()` for proper CSV formatting
-   ✅ Adds UTF-8 BOM for Excel compatibility
-   ✅ Proper headers for file download

## Testing the Fix:

### Test 1: Browser Direct Access

```
GET http://localhost:8000/api/main/student/import/template
```

**Expected Result:**

-   Browser should prompt to download `student_import_template.csv`
-   File should download automatically
-   Opening the file in Excel should show proper formatting

### Test 2: Using cURL

```bash
curl -X GET http://localhost:8000/api/main/student/import/template \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o student_template.csv
```

**Expected Result:**

```
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100   XXX  100   XXX    0     0   XXXX      0 --:--:-- --:--:-- --:--:--  XXXX
```

Check the file:

```bash
cat student_template.csv
```

Should show:

```csv
nis,first_name,last_name,gender,program_id,parent_id,period,nik,kk,address,born_in,born_at,last_education,village_id,village,district,postal_code,phone,hostel_id,status
2024001,John,Doe,L,1,NIK001,2024,1234567890123456,1234567890123456,"Jl. Contoh No. 123",Jakarta,2005-01-15,SMP,1,"Desa Contoh","Kec. Contoh",12345,081234567890,1,Aktif
```

### Test 3: Frontend/Axios

```javascript
// React/Vue/Angular example
const downloadTemplate = async () => {
    try {
        const response = await axios.get("/api/main/student/import/template", {
            headers: {
                Authorization: `Bearer ${token}`,
            },
            responseType: "blob", // IMPORTANT: Set responseType to blob
        });

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "student_import_template.csv");
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error("Download failed:", error);
    }
};
```

### Test 4: Parent Template

```bash
curl -X GET http://localhost:8000/api/main/parent/import/template \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o parent_template.csv

cat parent_template.csv
```

Should show:

```csv
nik,kk,first_name,last_name,gender,parent_as,card_address,domicile_address,phone,email,occupation_id,education_id
1234567890123456,1234567890123456,Ahmad,Santoso,L,ayah,"Jl. Merdeka No. 123, Jakarta","Jl. Sudirman No. 456, Jakarta",081234567890,ahmad@example.com,1,1
1234567890123457,1234567890123456,Siti,Rahayu,P,ibu,"Jl. Merdeka No. 123, Jakarta","Jl. Sudirman No. 456, Jakarta",081234567891,siti@example.com,2,2
```

## Key Improvements:

### 1. Proper CSV Encoding

-   Uses `fputcsv()` which automatically handles:
    -   Commas in data (wrapped in quotes)
    -   Quotes in data (properly escaped)
    -   Line breaks in data

### 2. UTF-8 BOM

```php
fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
```

This ensures Excel opens the file with correct encoding.

### 3. Stream Response

```php
return response()->stream($callback, 200, [...]);
```

Proper way to send file downloads in Laravel.

### 4. Cache Headers

```php
'Cache-Control' => 'no-cache, no-store, must-revalidate',
'Pragma' => 'no-cache',
'Expires' => '0'
```

Ensures browser doesn't cache the response.

## Frontend Integration:

### axios (Recommended)

```javascript
axios
    .get("/api/main/student/import/template", {
        responseType: "blob", // Critical!
    })
    .then((response) => {
        const url = window.URL.createObjectURL(response.data);
        const link = document.createElement("a");
        link.href = url;
        link.download = "student_template.csv";
        link.click();
        window.URL.revokeObjectURL(url);
    });
```

### fetch API

```javascript
fetch("/api/main/student/import/template", {
    headers: {
        Authorization: `Bearer ${token}`,
    },
})
    .then((response) => response.blob())
    .then((blob) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = "student_template.csv";
        link.click();
        window.URL.revokeObjectURL(url);
    });
```

### jQuery

```javascript
$.ajax({
    url: "/api/main/student/import/template",
    method: "GET",
    xhrFields: {
        responseType: "blob", // Critical!
    },
    success: function (data) {
        const url = window.URL.createObjectURL(data);
        const link = document.createElement("a");
        link.href = url;
        link.download = "student_template.csv";
        link.click();
        window.URL.revokeObjectURL(url);
    },
});
```

## Troubleshooting:

### Issue: Still showing text in browser

**Solution:** Make sure you're using `responseType: 'blob'` in your frontend code.

### Issue: 404 Error

**Solution:**

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Issue: File downloaded but empty

**Solution:** Check if the route is protected by authentication middleware and you're sending the token.

### Issue: Encoding problems in Excel

**Solution:** The UTF-8 BOM should fix this. If not, try opening with "Data > From Text/CSV" in Excel.

## Production Deployment:

After deploying to production, make sure to:

1. Clear all caches:

```bash
php artisan optimize:clear
php artisan optimize
```

2. Test both endpoints:

```bash
# Student template
curl -X GET https://your-domain.com/api/main/student/import/template \
  -H "Authorization: Bearer TOKEN" \
  -o student_template.csv

# Parent template
curl -X GET https://your-domain.com/api/main/parent/import/template \
  -H "Authorization: Bearer TOKEN" \
  -o parent_template.csv
```

3. Verify file contents:

```bash
head -n 2 student_template.csv
head -n 2 parent_template.csv
```

## Success Indicators:

✅ File downloads automatically in browser  
✅ Correct filename appears in download  
✅ CSV opens properly in Excel  
✅ Headers are on the first row  
✅ Sample data is properly formatted  
✅ Commas in addresses are handled correctly  
✅ No encoding issues

The fix is complete and ready for testing! 🎉
