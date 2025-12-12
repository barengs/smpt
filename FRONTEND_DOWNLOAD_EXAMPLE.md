# Frontend Integration - Download Template

## Quick Copy-Paste Examples

### React (with axios)

```javascript
import axios from "axios";

// Student Template Download
const downloadStudentTemplate = async () => {
    try {
        const response = await axios.get("/api/main/student/import/template", {
            headers: {
                Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
            responseType: "blob", // MUST set this!
        });

        // Create download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "student_import_template.csv");
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error("Download error:", error);
        alert("Gagal mengunduh template");
    }
};

// Parent Template Download
const downloadParentTemplate = async () => {
    try {
        const response = await axios.get("/api/main/parent/import/template", {
            headers: {
                Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
            responseType: "blob", // MUST set this!
        });

        // Create download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "parent_import_template.csv");
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error("Download error:", error);
        alert("Gagal mengunduh template");
    }
};

// React Component Example
import React from "react";

const ImportPage = () => {
    const handleDownloadTemplate = async () => {
        try {
            const response = await axios.get(
                "/api/main/student/import/template",
                {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem(
                            "token"
                        )}`,
                    },
                    responseType: "blob",
                }
            );

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.download = "student_import_template.csv";
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error("Download failed:", error);
        }
    };

    return (
        <div>
            <button onClick={handleDownloadTemplate}>
                Download Student Template
            </button>
        </div>
    );
};
```

---

### Vue.js (with axios)

```javascript
// Vue 3 Composition API
import { ref } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const downloading = ref(false);

    const downloadStudentTemplate = async () => {
      downloading.value = true;
      try {
        const response = await axios.get('/api/main/student/import/template', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          },
          responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.download = 'student_import_template.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } catch (error) {
        console.error('Download error:', error);
        alert('Gagal mengunduh template');
      } finally {
        downloading.value = false;
      }
    };

    return {
      downloading,
      downloadStudentTemplate
    };
  }
};

// Vue 2 Options API
export default {
  data() {
    return {
      downloading: false
    };
  },
  methods: {
    async downloadStudentTemplate() {
      this.downloading = true;
      try {
        const response = await this.$axios.get('/api/main/student/import/template', {
          responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.download = 'student_import_template.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } catch (error) {
        console.error('Download error:', error);
        this.$message.error('Gagal mengunduh template');
      } finally {
        this.downloading = false;
      }
    }
  }
};
```

---

### Vanilla JavaScript (fetch)

```javascript
// Student Template
async function downloadStudentTemplate() {
    try {
        const token = localStorage.getItem("token");

        const response = await fetch("/api/main/student/import/template", {
            method: "GET",
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        if (!response.ok) {
            throw new Error("Download failed");
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = "student_import_template.csv";
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error("Download error:", error);
        alert("Gagal mengunduh template");
    }
}

// Parent Template
async function downloadParentTemplate() {
    try {
        const token = localStorage.getItem("token");

        const response = await fetch("/api/main/parent/import/template", {
            method: "GET",
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        if (!response.ok) {
            throw new Error("Download failed");
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = "parent_import_template.csv";
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error("Download error:", error);
        alert("Gagal mengunduh template");
    }
}

// Add to button
document
    .getElementById("btnDownloadStudent")
    .addEventListener("click", downloadStudentTemplate);
document
    .getElementById("btnDownloadParent")
    .addEventListener("click", downloadParentTemplate);
```

---

### jQuery

```javascript
// Student Template
$("#btnDownloadStudent").on("click", function () {
    $.ajax({
        url: "/api/main/student/import/template",
        method: "GET",
        headers: {
            Authorization: "Bearer " + localStorage.getItem("token"),
        },
        xhrFields: {
            responseType: "blob",
        },
        success: function (data) {
            const url = window.URL.createObjectURL(data);
            const link = document.createElement("a");
            link.href = url;
            link.download = "student_import_template.csv";
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        },
        error: function (xhr, status, error) {
            console.error("Download error:", error);
            alert("Gagal mengunduh template");
        },
    });
});

// Parent Template
$("#btnDownloadParent").on("click", function () {
    $.ajax({
        url: "/api/main/parent/import/template",
        method: "GET",
        headers: {
            Authorization: "Bearer " + localStorage.getItem("token"),
        },
        xhrFields: {
            responseType: "blob",
        },
        success: function (data) {
            const url = window.URL.createObjectURL(data);
            const link = document.createElement("a");
            link.href = url;
            link.download = "parent_import_template.csv";
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        },
        error: function (xhr, status, error) {
            console.error("Download error:", error);
            alert("Gagal mengunduh template");
        },
    });
});
```

---

### Angular

```typescript
// download.service.ts
import { Injectable } from "@angular/core";
import { HttpClient, HttpHeaders } from "@angular/common/http";

@Injectable({
    providedIn: "root",
})
export class DownloadService {
    constructor(private http: HttpClient) {}

    downloadStudentTemplate() {
        const token = localStorage.getItem("token");
        const headers = new HttpHeaders({
            Authorization: `Bearer ${token}`,
        });

        this.http
            .get("/api/main/student/import/template", {
                headers,
                responseType: "blob",
            })
            .subscribe({
                next: (data) => {
                    const url = window.URL.createObjectURL(data);
                    const link = document.createElement("a");
                    link.href = url;
                    link.download = "student_import_template.csv";
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.URL.revokeObjectURL(url);
                },
                error: (error) => {
                    console.error("Download error:", error);
                    alert("Gagal mengunduh template");
                },
            });
    }

    downloadParentTemplate() {
        const token = localStorage.getItem("token");
        const headers = new HttpHeaders({
            Authorization: `Bearer ${token}`,
        });

        this.http
            .get("/api/main/parent/import/template", {
                headers,
                responseType: "blob",
            })
            .subscribe({
                next: (data) => {
                    const url = window.URL.createObjectURL(data);
                    const link = document.createElement("a");
                    link.href = url;
                    link.download = "parent_import_template.csv";
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.URL.revokeObjectURL(url);
                },
                error: (error) => {
                    console.error("Download error:", error);
                    alert("Gagal mengunduh template");
                },
            });
    }
}

// component.ts
import { Component } from "@angular/core";
import { DownloadService } from "./download.service";

@Component({
    selector: "app-import",
    template: `
        <button (click)="downloadTemplate()">Download Template</button>
    `,
})
export class ImportComponent {
    constructor(private downloadService: DownloadService) {}

    downloadTemplate() {
        this.downloadService.downloadStudentTemplate();
    }
}
```

---

## Common Pitfalls ⚠️

### ❌ Wrong: Missing responseType

```javascript
// This will show text instead of downloading
axios.get("/api/main/student/import/template");
```

### ✅ Correct: With responseType

```javascript
axios.get("/api/main/student/import/template", {
    responseType: "blob",
});
```

---

### ❌ Wrong: Not revoking URL

```javascript
// Memory leak
const url = window.URL.createObjectURL(blob);
link.href = url;
link.click();
// URL not revoked!
```

### ✅ Correct: Revoke URL

```javascript
const url = window.URL.createObjectURL(blob);
link.href = url;
link.click();
window.URL.revokeObjectURL(url); // Clean up
```

---

## Complete React Component Example

```javascript
import React, { useState } from "react";
import axios from "axios";

const StudentImport = () => {
    const [loading, setLoading] = useState(false);
    const [file, setFile] = useState(null);

    // Download template
    const handleDownloadTemplate = async () => {
        setLoading(true);
        try {
            const response = await axios.get(
                "/api/main/student/import/template",
                {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem(
                            "token"
                        )}`,
                    },
                    responseType: "blob",
                }
            );

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.download = "student_import_template.csv";
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            alert("Template berhasil diunduh");
        } catch (error) {
            console.error("Download error:", error);
            alert("Gagal mengunduh template");
        } finally {
            setLoading(false);
        }
    };

    // Handle file selection
    const handleFileChange = (e) => {
        setFile(e.target.files[0]);
    };

    // Upload and import
    const handleImport = async () => {
        if (!file) {
            alert("Pilih file terlebih dahulu");
            return;
        }

        setLoading(true);
        try {
            const formData = new FormData();
            formData.append("file", file);

            const response = await axios.post(
                "/api/main/student/import",
                formData,
                {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem(
                            "token"
                        )}`,
                        "Content-Type": "multipart/form-data",
                    },
                }
            );

            alert(
                `Import berhasil! ${response.data.data.success_count} data berhasil diimport`
            );
            setFile(null);
        } catch (error) {
            console.error("Import error:", error);
            alert("Gagal mengimport data");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="import-container">
            <h2>Import Data Siswa</h2>

            {/* Download Template */}
            <div className="download-section">
                <button onClick={handleDownloadTemplate} disabled={loading}>
                    {loading ? "Downloading..." : "Download Template"}
                </button>
            </div>

            {/* Upload File */}
            <div className="upload-section">
                <input
                    type="file"
                    accept=".csv,.xlsx,.xls"
                    onChange={handleFileChange}
                    disabled={loading}
                />
                <button onClick={handleImport} disabled={loading || !file}>
                    {loading ? "Importing..." : "Import Data"}
                </button>
            </div>
        </div>
    );
};

export default StudentImport;
```

---

## Testing

Test with browser console:

```javascript
// Quick test
fetch("/api/main/student/import/template", {
    headers: {
        Authorization: "Bearer YOUR_TOKEN",
    },
})
    .then((r) => r.blob())
    .then((blob) => {
        console.log("Blob size:", blob.size);
        console.log("Blob type:", blob.type);

        // Download
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "test.csv";
        a.click();
        URL.revokeObjectURL(url);
    });
```

Expected console output:

```
Blob size: XXX (some number)
Blob type: text/csv
```

If you see this and the file downloads, it's working! ✅
