# AGENTS.md — Workspace Rules (smp-be)

## Tech Stack Backend Utama (smp-be)
- Laravel 12, PHP 8.4 (path: `C:\Program Files\php-8.4.24`)
- Auth: JWT (`tymon/jwt-auth`)
- RBAC: `spatie/laravel-permission`
- Activity Log: `spatie/laravel-activitylog`
- Export/Import: `maatwebsite/excel`
- Database: MySQL (semua environment)
  - Dev: `DB_DATABASE=pesantren`, Host `127.0.0.1:3306`
- Queue: database driver

## Rules Pengembangan Backend
1. Semua route API ada di `routes/api.php` — dua prefix utama: `/api/master/*` dan `/api/main/*`
2. Controllers di `app/Http/Controllers/Api/Main/` (fitur) dan `app/Http/Controllers/Api/Master/` (data master)
3. Selalu gunakan Form Request untuk validasi (`app/Http/Requests/`)
4. Selalu gunakan API Resource untuk response transformation (`app/Http/Resources/`)
5. Soft delete pattern: `trashed`, `restore`, `forceDelete`
6. Export/import Excel pattern: `export`, `backup`, `import`, `downloadTemplate`

## Koneksi ke Bank Santri
- Header `X-Internal-Key` untuk memanggil bank-santri internal API
- Endpoint `/api/internal/*` di bank-santri
- Callback dari bank-santri masuk ke `POST /api/internal/transaction/activate-callback`

## Sistem Akses (Access Control)
- Cek `User::isSuperAdmin()` untuk bypass scope data
- `User::getAccessibleInstitutionIds()` → array institusi (null = semua)
- `User::getAccessibleProgramIds()` → array program (null = semua)
- Hierarki: Pusat > Program > Institusi

## Nomor Dokumen Hijriah
- Izin Santri: `SIZ{YYYY}{MM}{DD}{XXX}` (SIZ = Surat Izin, format Hijriah)
- Perjanjian: `AGR{YYYY}{MM}{DD}{XXX}`
- Gunakan `IntlDateFormatter` dengan `en_SA@calendar=islamic-civil`
