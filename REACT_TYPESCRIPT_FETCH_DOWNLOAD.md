# React + TypeScript + Fetch - Template Download Solution

## Complete Working Solution

### 1. Create a Download Utility Hook

Create a custom hook for downloading templates:

```typescript
// hooks/useTemplateDownload.ts
import { useState } from "react";

interface DownloadOptions {
    url: string;
    filename: string;
    token?: string;
}

export const useTemplateDownload = () => {
    const [isDownloading, setIsDownloading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const downloadTemplate = async ({
        url,
        filename,
        token,
    }: DownloadOptions) => {
        setIsDownloading(true);
        setError(null);

        try {
            // Prepare headers
            const headers: HeadersInit = {
                Accept: "text/csv",
            };

            // Add authorization if token is provided
            if (token) {
                headers["Authorization"] = `Bearer ${token}`;
            }

            // Fetch the file
            const response = await fetch(url, {
                method: "GET",
                headers: headers,
            });

            // Check if request was successful
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Get the blob from response
            const blob = await response.blob();

            // Create a temporary URL for the blob
            const downloadUrl = window.URL.createObjectURL(blob);

            // Create a temporary anchor element and trigger download
            const link = document.createElement("a");
            link.href = downloadUrl;
            link.download = filename;

            // Append to body, click, and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Clean up the URL
            window.URL.revokeObjectURL(downloadUrl);

            return true;
        } catch (err) {
            const errorMessage =
                err instanceof Error ? err.message : "Download failed";
            setError(errorMessage);
            console.error("Download error:", err);
            return false;
        } finally {
            setIsDownloading(false);
        }
    };

    return { downloadTemplate, isDownloading, error };
};
```

### 2. Create Import Component

```typescript
// components/StudentImport.tsx
import React, { useState } from "react";
import { useTemplateDownload } from "../hooks/useTemplateDownload";

const StudentImport: React.FC = () => {
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [importing, setImporting] = useState(false);
    const [importResult, setImportResult] = useState<string | null>(null);

    const {
        downloadTemplate,
        isDownloading,
        error: downloadError,
    } = useTemplateDownload();

    // Get token from localStorage or your auth context
    const token = localStorage.getItem("token") || "";

    // Download student template
    const handleDownloadStudentTemplate = async () => {
        const success = await downloadTemplate({
            url: "/api/main/student/import/template",
            filename: "student_import_template.csv",
            token: token,
        });

        if (success) {
            alert("Template berhasil diunduh!");
        } else {
            alert("Gagal mengunduh template: " + downloadError);
        }
    };

    // Download parent template
    const handleDownloadParentTemplate = async () => {
        const success = await downloadTemplate({
            url: "/api/main/parent/import/template",
            filename: "parent_import_template.csv",
            token: token,
        });

        if (success) {
            alert("Template berhasil diunduh!");
        } else {
            alert("Gagal mengunduh template: " + downloadError);
        }
    };

    // Handle file selection
    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        if (event.target.files && event.target.files.length > 0) {
            setSelectedFile(event.target.files[0]);
        }
    };

    // Import data
    const handleImport = async () => {
        if (!selectedFile) {
            alert("Pilih file terlebih dahulu");
            return;
        }

        setImporting(true);
        setImportResult(null);

        try {
            const formData = new FormData();
            formData.append("file", selectedFile);

            const response = await fetch("/api/main/student/import", {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${token}`,
                    // Note: Don't set Content-Type for FormData, browser will set it automatically
                },
                body: formData,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                setImportResult(
                    `Import berhasil!\n` +
                        `Sukses: ${data.data.success_count}\n` +
                        `Gagal: ${data.data.failure_count}\n` +
                        `Total: ${data.data.total}`
                );
                setSelectedFile(null);
            } else {
                throw new Error(data.message || "Import gagal");
            }
        } catch (err) {
            const errorMessage =
                err instanceof Error ? err.message : "Import failed";
            setImportResult(`Error: ${errorMessage}`);
            console.error("Import error:", err);
        } finally {
            setImporting(false);
        }
    };

    return (
        <div className="import-container">
            <h2>Import Data Siswa</h2>

            {/* Download Template Section */}
            <div className="download-section">
                <h3>1. Download Template</h3>
                <button
                    onClick={handleDownloadStudentTemplate}
                    disabled={isDownloading}
                    className="btn-download"
                >
                    {isDownloading
                        ? "Downloading..."
                        : "Download Template Siswa"}
                </button>

                <button
                    onClick={handleDownloadParentTemplate}
                    disabled={isDownloading}
                    className="btn-download"
                    style={{ marginLeft: "10px" }}
                >
                    {isDownloading
                        ? "Downloading..."
                        : "Download Template Orang Tua"}
                </button>
            </div>

            {/* Upload Section */}
            <div className="upload-section">
                <h3>2. Upload File</h3>
                <input
                    type="file"
                    accept=".csv,.xlsx,.xls"
                    onChange={handleFileChange}
                    disabled={importing}
                />
                {selectedFile && <p>File: {selectedFile.name}</p>}
            </div>

            {/* Import Button */}
            <div className="import-section">
                <h3>3. Import</h3>
                <button
                    onClick={handleImport}
                    disabled={importing || !selectedFile}
                    className="btn-import"
                >
                    {importing ? "Importing..." : "Import Data"}
                </button>
            </div>

            {/* Result */}
            {importResult && (
                <div className="import-result">
                    <h4>Result:</h4>
                    <pre>{importResult}</pre>
                </div>
            )}

            {/* Download Error */}
            {downloadError && (
                <div className="error-message">
                    <p>Download Error: {downloadError}</p>
                </div>
            )}
        </div>
    );
};

export default StudentImport;
```

### 3. Alternative: Inline Download Function

If you don't want to use a custom hook:

```typescript
// components/SimpleImport.tsx
import React, { useState } from "react";

const SimpleImport: React.FC = () => {
    const [downloading, setDownloading] = useState(false);

    const downloadTemplate = async (
        endpoint: string,
        filename: string
    ): Promise<void> => {
        setDownloading(true);

        try {
            const token = localStorage.getItem("token") || "";

            const response = await fetch(endpoint, {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: "text/csv",
                },
            });

            if (!response.ok) {
                throw new Error(
                    `Download failed: ${response.status} ${response.statusText}`
                );
            }

            // Convert response to blob
            const blob = await response.blob();

            // Create download link
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href = url;
            link.download = filename;

            // Trigger download
            document.body.appendChild(link);
            link.click();

            // Cleanup
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);

            console.log("Download successful");
        } catch (error) {
            console.error("Download error:", error);
            alert(
                "Gagal mengunduh template: " +
                    (error instanceof Error ? error.message : "Unknown error")
            );
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
                {downloading ? "Downloading..." : "Download Student Template"}
            </button>

            <button
                onClick={() =>
                    downloadTemplate(
                        "/api/main/parent/import/template",
                        "parent_template.csv"
                    )
                }
                disabled={downloading}
                style={{ marginLeft: "10px" }}
            >
                {downloading ? "Downloading..." : "Download Parent Template"}
            </button>
        </div>
    );
};

export default SimpleImport;
```

### 4. With API Base URL Configuration

```typescript
// config/api.ts
export const API_BASE_URL =
    process.env.REACT_APP_API_URL || "http://localhost:8000";

export const API_ENDPOINTS = {
    student: {
        import: `${API_BASE_URL}/api/main/student/import`,
        template: `${API_BASE_URL}/api/main/student/import/template`,
    },
    parent: {
        import: `${API_BASE_URL}/api/main/parent/import`,
        template: `${API_BASE_URL}/api/main/parent/import/template`,
    },
};
```

```typescript
// components/ImportWithConfig.tsx
import React from "react";
import { API_ENDPOINTS } from "../config/api";

const ImportWithConfig: React.FC = () => {
    const downloadTemplate = async (url: string, filename: string) => {
        try {
            const token = localStorage.getItem("token") || "";

            const response = await fetch(url, {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

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
            console.error("Download failed:", error);
            alert("Download failed");
        }
    };

    return (
        <div>
            <button
                onClick={() =>
                    downloadTemplate(
                        API_ENDPOINTS.student.template,
                        "student_template.csv"
                    )
                }
            >
                Download Student Template
            </button>
        </div>
    );
};

export default ImportWithConfig;
```

### 5. Complete Type Definitions

```typescript
// types/import.types.ts
export interface ImportResponse {
    success: boolean;
    message: string;
    data: {
        success_count: number;
        failure_count: number;
        total: number;
        errors?: string[];
        total_errors?: number;
        info?: string;
    };
}

export interface DownloadTemplateParams {
    url: string;
    filename: string;
    token?: string;
}

export interface ImportParams {
    file: File;
    endpoint: string;
    token: string;
}
```

### 6. Error Handling Enhanced

```typescript
// utils/downloadHelper.ts
export const downloadFile = async (
    url: string,
    filename: string,
    token?: string
): Promise<{ success: boolean; error?: string }> => {
    try {
        const headers: HeadersInit = {};

        if (token) {
            headers["Authorization"] = `Bearer ${token}`;
        }

        const response = await fetch(url, {
            method: "GET",
            headers,
        });

        // Check for different error types
        if (response.status === 401) {
            return {
                success: false,
                error: "Unauthorized. Please login again.",
            };
        }

        if (response.status === 404) {
            return { success: false, error: "Template not found." };
        }

        if (!response.ok) {
            return {
                success: false,
                error: `Server error: ${response.status} ${response.statusText}`,
            };
        }

        // Check content type
        const contentType = response.headers.get("content-type");
        if (
            !contentType?.includes("text/csv") &&
            !contentType?.includes("application/octet-stream")
        ) {
            console.warn("Unexpected content type:", contentType);
        }

        const blob = await response.blob();

        // Check if blob is empty
        if (blob.size === 0) {
            return { success: false, error: "Downloaded file is empty." };
        }

        // Create and trigger download
        const url_obj = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url_obj;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url_obj);

        return { success: true };
    } catch (error) {
        console.error("Download error:", error);
        return {
            success: false,
            error:
                error instanceof Error
                    ? error.message
                    : "Unknown error occurred",
        };
    }
};
```

Usage:

```typescript
import { downloadFile } from "../utils/downloadHelper";

const handleDownload = async () => {
    const result = await downloadFile(
        "/api/main/student/import/template",
        "student_template.csv",
        localStorage.getItem("token") || undefined
    );

    if (result.success) {
        alert("Download successful!");
    } else {
        alert(`Download failed: ${result.error}`);
    }
};
```

## Debugging Tips

### Check if file is actually downloading:

```typescript
const downloadTemplate = async () => {
    try {
        const response = await fetch("/api/main/student/import/template", {
            headers: {
                Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
        });

        console.log("Response status:", response.status);
        console.log("Response headers:", response.headers);
        console.log("Content-Type:", response.headers.get("content-type"));

        const blob = await response.blob();
        console.log("Blob size:", blob.size);
        console.log("Blob type:", blob.type);

        if (blob.size === 0) {
            console.error("Blob is empty!");
            return;
        }

        // Download
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = "test.csv";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        console.log("Download triggered");
    } catch (error) {
        console.error("Error:", error);
    }
};
```

## Common Issues & Solutions

### Issue 1: CORS Error

```
Access to fetch at '...' from origin '...' has been blocked by CORS policy
```

**Solution:** Make sure your Laravel API has proper CORS configuration.

### Issue 2: 404 Not Found

**Solution:**

-   Check the URL is correct
-   Make sure routes are cleared: `php artisan route:clear`
-   Check if middleware is blocking the route

### Issue 3: Empty File Downloaded

**Solution:**

-   Check backend is returning proper response
-   Check `Content-Type` header
-   Log the blob size before creating download

### Issue 4: File shows as text in browser

**Solution:**

-   Make sure you're creating a blob: `await response.blob()`
-   Use `window.URL.createObjectURL(blob)`
-   Set proper `download` attribute on link

## Test in Browser Console

```javascript
// Quick test
fetch("/api/main/student/import/template", {
    headers: {
        Authorization: "Bearer YOUR_TOKEN",
    },
})
    .then((r) => {
        console.log("Status:", r.status);
        console.log("Headers:", r.headers);
        return r.blob();
    })
    .then((blob) => {
        console.log("Blob size:", blob.size, "bytes");
        console.log("Blob type:", blob.type);

        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "test.csv";
        a.click();
        URL.revokeObjectURL(url);
    });
```

Expected output:

```
Status: 200
Headers: Headers { ... }
Blob size: XXX bytes
Blob type: text/csv
```

If you see this, the download should work! ✅
