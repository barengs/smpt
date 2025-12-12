# Quick Fix - React TypeScript Fetch Download

## ⚡ Copy This Function - It Works!

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
            throw new Error(`HTTP error! status: ${response.status}`);
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
    } catch (error) {
        console.error("Download failed:", error);
        alert("Download gagal");
    }
};
```

## 📝 Usage Example

```typescript
// In your component:
<button onClick={() => downloadTemplate(
  '/api/main/student/import/template',
  'student_template.csv'
)}>
  Download Student Template
</button>

<button onClick={() => downloadTemplate(
  '/api/main/parent/import/template',
  'parent_template.csv'
)}>
  Download Parent Template
</button>
```

## 🔍 Debugging Steps

If it's not working, check these in browser console:

```typescript
// Step 1: Check if endpoint is reachable
fetch("/api/main/student/import/template", {
    headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
}).then((r) => console.log("Status:", r.status, "OK:", r.ok));

// Step 2: Check blob
fetch("/api/main/student/import/template", {
    headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
})
    .then((r) => r.blob())
    .then((blob) => console.log("Blob size:", blob.size, "Type:", blob.type));

// Step 3: Full test
fetch("/api/main/student/import/template", {
    headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
})
    .then((r) => r.blob())
    .then((blob) => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "test.csv";
        a.click();
        URL.revokeObjectURL(url);
    });
```

## ✅ Expected Console Output

```
Status: 200 OK: true
Blob size: 324 Type: text/csv
```

If you see this → Download should work! ✅

## ❌ Common Problems

### Problem 1: Status 404

**Fix:**

```bash
php artisan route:clear
php artisan config:clear
```

### Problem 2: Status 401

**Fix:** Check your token

```typescript
console.log("Token:", localStorage.getItem("token"));
```

### Problem 3: CORS Error

**Fix:** Add to Laravel `cors.php`:

```php
'paths' => ['api/*', 'api/main/*'],
'allowed_origins' => ['*'],
```

### Problem 4: Empty file (0 bytes)

**Fix:** Check backend response is correct

## 🎯 Complete Working Component

```typescript
import React, { useState } from "react";

export const ImportPage: React.FC = () => {
    const [downloading, setDownloading] = useState(false);

    const downloadTemplate = async (url: string, filename: string) => {
        setDownloading(true);
        try {
            const response = await fetch(url, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("token")}`,
                },
            });

            if (!response.ok) throw new Error("Download failed");

            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href = downloadUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(downloadUrl);
        } catch (error) {
            alert("Download gagal");
        } finally {
            setDownloading(false);
        }
    };

    return (
        <div>
            <button
                onClick={() =>
                    downloadTemplate(
                        "/api/main/student/import/template",
                        "student_template.csv"
                    )
                }
                disabled={downloading}
            >
                {downloading ? "Downloading..." : "Download Template"}
            </button>
        </div>
    );
};
```

## 📞 Still Not Working?

Check in this order:

1. ✅ Backend route exists: `php artisan route:list --path=template`
2. ✅ Token is valid: `console.log(localStorage.getItem('token'))`
3. ✅ URL is correct: Check network tab in DevTools
4. ✅ Response is blob: Check response type in network tab
5. ✅ No CORS error: Check console for errors

---

**Copy the function above and it will work!** 🎉
