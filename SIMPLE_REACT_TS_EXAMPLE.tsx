// ============================================================================
// SIMPLE COPY-PASTE SOLUTION FOR REACT + TYPESCRIPT + FETCH
// ============================================================================

import React, { useState } from 'react';

const StudentImportPage: React.FC = () => {
  const [isDownloading, setIsDownloading] = useState(false);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isImporting, setIsImporting] = useState(false);

  // ========================================================================
  // DOWNLOAD TEMPLATE FUNCTION - COPY THIS!
  // ========================================================================
  const downloadTemplate = async (
    endpoint: string,
    filename: string
  ): Promise<void> => {
    setIsDownloading(true);

    try {
      // Get token from localStorage
      const token = localStorage.getItem('token') || '';

      // Fetch the template
      const response = await fetch(endpoint, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'text/csv',
        },
      });

      // Check if response is OK
      if (!response.ok) {
        throw new Error(`Failed to download: ${response.status}`);
      }

      // Convert to blob
      const blob = await response.blob();

      // Create temporary download link
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;

      // Trigger download
      document.body.appendChild(link);
      link.click();

      // Cleanup
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);

      alert('Template berhasil diunduh!');
    } catch (error) {
      console.error('Download error:', error);
      alert('Gagal mengunduh template: ' + (error instanceof Error ? error.message : 'Unknown error'));
    } finally {
      setIsDownloading(false);
    }
  };

  // ========================================================================
  // IMPORT FUNCTION
  // ========================================================================
  const handleImport = async (): Promise<void> => {
    if (!selectedFile) {
      alert('Pilih file terlebih dahulu');
      return;
    }

    setIsImporting(true);

    try {
      const token = localStorage.getItem('token') || '';
      const formData = new FormData();
      formData.append('file', selectedFile);

      const response = await fetch('/api/main/student/import', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
        body: formData,
      });

      if (!response.ok) {
        throw new Error(`Import failed: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        alert(
          `Import berhasil!\n` +
          `Sukses: ${data.data.success_count}\n` +
          `Gagal: ${data.data.failure_count}`
        );
        setSelectedFile(null);
      } else {
        throw new Error(data.message || 'Import failed');
      }
    } catch (error) {
      console.error('Import error:', error);
      alert('Import gagal: ' + (error instanceof Error ? error.message : 'Unknown error'));
    } finally {
      setIsImporting(false);
    }
  };

  // ========================================================================
  // RENDER
  // ========================================================================
  return (
    <div style={{ padding: '20px' }}>
      <h1>Import Data Siswa</h1>

      {/* Download Template Section */}
      <div style={{ marginBottom: '30px' }}>
        <h2>1. Download Template</h2>
        <button
          onClick={() => downloadTemplate(
            '/api/main/student/import/template',
            'student_import_template.csv'
          )}
          disabled={isDownloading}
          style={{
            padding: '10px 20px',
            marginRight: '10px',
            cursor: isDownloading ? 'not-allowed' : 'pointer'
          }}
        >
          {isDownloading ? 'Downloading...' : 'Download Template Siswa'}
        </button>

        <button
          onClick={() => downloadTemplate(
            '/api/main/parent/import/template',
            'parent_import_template.csv'
          )}
          disabled={isDownloading}
          style={{
            padding: '10px 20px',
            cursor: isDownloading ? 'not-allowed' : 'pointer'
          }}
        >
          {isDownloading ? 'Downloading...' : 'Download Template Orang Tua'}
        </button>
      </div>

      {/* Upload Section */}
      <div style={{ marginBottom: '30px' }}>
        <h2>2. Pilih File</h2>
        <input
          type="file"
          accept=".csv,.xlsx,.xls"
          onChange={(e) => {
            if (e.target.files && e.target.files.length > 0) {
              setSelectedFile(e.target.files[0]);
            }
          }}
          disabled={isImporting}
        />
        {selectedFile && (
          <p style={{ marginTop: '10px' }}>
            File terpilih: <strong>{selectedFile.name}</strong>
          </p>
        )}
      </div>

      {/* Import Section */}
      <div>
        <h2>3. Import Data</h2>
        <button
          onClick={handleImport}
          disabled={isImporting || !selectedFile}
          style={{
            padding: '10px 20px',
            cursor: (isImporting || !selectedFile) ? 'not-allowed' : 'pointer',
            backgroundColor: (isImporting || !selectedFile) ? '#ccc' : '#007bff',
            color: 'white',
            border: 'none',
            borderRadius: '4px'
          }}
        >
          {isImporting ? 'Importing...' : 'Import Data'}
        </button>
      </div>
    </div>
  );
};

export default StudentImportPage;


// ============================================================================
// ALTERNATIVE: MINIMAL VERSION (Just copy the function)
// ============================================================================

/*
// Copy this function to your component:

const downloadTemplate = async (endpoint: string, filename: string) => {
  try {
    const token = localStorage.getItem('token') || '';

    const response = await fetch(endpoint, {
      method: 'GET',
      headers: { 'Authorization': `Bearer ${token}` },
    });

    if (!response.ok) throw new Error('Download failed');

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    alert('Download failed');
    console.error(error);
  }
};

// Usage:
<button onClick={() => downloadTemplate(
  '/api/main/student/import/template',
  'student_template.csv'
)}>
  Download Template
</button>
*/
