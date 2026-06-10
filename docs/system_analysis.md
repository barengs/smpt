# Analisis Sistem & Ekosistem Aplikasi Pesantren (SMPT, SMP-Apps-FE, & Bank Santri)

Dokumen ini berisi analisis menyeluruh terhadap seluruh modul dan arsitektur ekosistem manajemen pesantren. Ekosistem ini dirancang dengan arsitektur terdistribusi yang terdiri dari tiga komponen utama:
1. **Frontend (React.js - smp-apps-fe)**: Berjalan di client-side, menghubungkan seluruh user interface untuk staf sekolah, admin keuangan, guru, dan orang tua (wali santri).
2. **Backend Akademik & Kesiswaan (Laravel - smpt)**: Berjalan di port `8000`, mengelola data master sekolah, presensi, kurikulum, ketertiban (kamtib), perizinan santri, dan laporan.
3. **Backend Perbankan (Laravel - bank-santri)**: Berjalan di port `8001`, mengelola rekening tabungan santri (e-wallet), alur akuntansi (ledger double-entry), transaksi koperasi, pembayaran paket sekolah, dan integrasi payment gateway.

---

## 1. Konfigurasi Berkas `.env` & Integrasi Lingkungan

Konfigurasi komunikasi antar-sistem ditentukan oleh nilai-nilai variabel lingkungan di masing-masing sistem. Berikut adalah ringkasan variabel komunikasi di berkas `.env` dari setiap subsistem:

### A. Frontend (`smp-apps-fe/.env`)
Mengatur gerbang endpoint API utama yang akan dihubungi oleh client (Redux RTK Query):
```ini
VITE_API_BASE_URL=http://localhost:8000/api
VITE_STORAGE_BASE_URL=http://localhost:8000/storage/
VITE_BANK_API_BASE_URL=http://localhost:8001/api/
VITE_BANK_WEB_URL=http://localhost:8001
```
*Implikasi*: Client React terhubung secara paralel ke port `8000` (akademik) dan port `8001` (perbankan).

### B. Backend Akademik (`smpt/.env` & `config/services.php`)
Secara default, Laravel berjalan di port `8000`. Jika variabel tidak disetel di `.env`, sistem secara otomatis menggunakan fallback nilai default di `config/services.php`:
```php
// config/services.php
'bank_santri' => [
    'url' => env('BANK_SANTRI_URL', 'http://localhost:8001'),
    'internal_key' => env('BANK_SANTRI_INTERNAL_KEY', 'smpt-banksantri-internal-secret-2026'),
]
```
*Implikasi*: Saat ada konfirmasi transaksi registrasi santri baru, backend SMPT akan mengirimkan request HTTP internal ke port `8001` menggunakan otentikasi header `X-Internal-Key` bernilai `smpt-banksantri-internal-secret-2026`.

### C. Backend Bank Santri (`bank-santri/.env` & `config/services.php`)
Secara default, aplikasi perbankan berjalan di port `8001`. Pengaturan komunikasi balik ke sistem akademik diatur di `config/services.php`:
```php
// config/services.php
'smpt' => [
    'url' => env('SMPT_URL', 'http://localhost:8000'),
    'internal_key' => env('INTERNAL_API_KEY', 'smpt-banksantri-internal-secret-2026'),
]
```
*Implikasi*: Backend Bank Santri menghubungi port `8000` untuk melakukan validasi profil santri dan sinkronisasi data RFID/Kartu Santri. Keamanan komunikasi ini juga dijaga oleh header `X-Internal-Key`.

---

## 2. Modul Dinamis Berbasis Database (Pengaturan Hak Akses)

Untuk memberikan fleksibilitas penuh dalam pengaturan hak akses, seluruh struktur menu sidebar disimpan di dalam database pada tabel `menus`. Hak akses dikelola menggunakan relasi antara tabel `roles`, `permissions`, dan tabel pivot `role_menu`. 

Alur kerjanya adalah sebagai berikut:
- **Autentikasi**: Setelah login, frontend memanggil API `main/user/menus` di backend SMPT.
- **Filtering**: Backend memfilter menu yang berhak dilihat oleh peran (`Role`) pengguna saat ini.
- **Tree Building**: Data menu dinormalisasi menjadi struktur pohon (*menu tree*) di frontend secara rekursif sebelum dirender di sidebar.

Berdasarkan skema `MenuSeeder.php`, berikut adalah daftar lengkap **15 Modul Utama** beserta submenu dinamisnya yang terdaftar di dalam sistem:

### 1. Dasbor (`/dashboard`)
*Menampilkan ringkasan statistik kesiswaan, grafik pelanggaran, sisa saldo santri, dan aktivitas terbaru.*

### 2. Guru Tugas (`/dashboard/guru-tugas`)
*Mengelola program pengabdian santri senior/guru yang ditugaskan ke luar daerah.*
- **Daftar Guru Tugas**: Pengelolaan nama dan penugasan guru tugas.
- **Penanggung Jawab**: Manajemen penanggung jawab atau supervisor program magang/tugas.
- **Institusi Tugas**: Lokasi/instansi mitra tempat penugasan.

### 3. Manajemen Staf (`/dashboard/staf`)
*Tata kelola kepegawaian dan kepemimpinan.*
- **Data Staf**: Profil detail pegawai dan guru.
- **Struktur Organisasi**: Hierarki bagan organisasi internal pesantren.

### 4. Kurikulum (`/dashboard/manajemen-kurikulum`)
*Mengelola operasional kegiatan belajar mengajar.*
- **Mata Pelajaran**: Pengelolaan mata pelajaran aktif.
- **Jadwal Pelajaran**: Pemetaan jadwal pelajaran per rombel.
- **Guru**: Profil data guru pengampu materi.
- **Penugasan Guru**: Penugasan guru ke kelas dan mata pelajaran.
- **Jam Mengajar**: Konfigurasi total jam mengajar masing-masing guru.
- **Presensi**: Pencatatan kehadiran siswa per kelas per pertemuan.
- **Penilaian**: Input nilai harian, PTS, dan PAS berdasarkan formula penilaian.
- **E-Raport**: Cetak raport digital santri.
- **Kenaikan Kelas**: Logika promosi santri ke kelas berikutnya.
- **Institusi Pendidikan**: Data jenjang sekolah formal (SMP, SMA, dll.) di lingkungan pesantren.

### 5. Bank Santri (`/dashboard/bank-santri`)
*Layanan transaksi keuangan santri di sekolah (antarmuka staf kasir/teller).*
- **Dashboard Bank**: Statistik kas bank, total tabungan nasabah, dan kasir aktif.
- **Transaksi Bank**: Riwayat transaksi penyetoran dan penarikan.
- **Paket Pembayaran**: Pengaturan biaya rutin (SPP, Uang Gedung).
- **Proses Pembayaran**: Halaman eksekusi pembayaran biaya oleh kasir.
- **Verifikasi Top-up**: Persetujuan permintaan top-up saldo via transfer bank.
- **Rekening Bank**: Manajemen data buku tabungan santri (nomor rekening = NIS).
- **Kasir Koperasi**: Antarmuka penjualan toko/koperasi sekolah.
- **Top-Up / Setor Tunai**: Eksekusi deposit saldo langsung via teller.
- **Transfer Bank**: Pilihan opsi top-up dengan transfer rekening bank virtual.

### 6. Laporan Keuangan Bank (`/dashboard/bank-santri/laporan`)
*Modul akuntansi Mini Bank untuk transparansi pembukuan.*
- **Jurnal Umum**: Jurnal kronologis transaksi double-entry.
- **Mutasi Nasabah**: Laporan mutasi kredit/debit tabungan per nomor rekening.
- **Rekap Saldo**: Rekapitulasi total saldo kas bank santri.
- **Rekap Kasir**: Rekapitulasi setoran uang harian masing-masing kasir/teller.
- **Konfigurasi Transaksi**: Pemetaan aturan jurnal akuntansi.

### 7. Manajemen Bank (`/dashboard/bank-santri/settings`)
*Konfigurasi sistem perbankan syariah.*
- **Produk Bank**: Produk tabungan (wadiah/mudharabah) dengan bagi hasil atau biaya admin.
- **COA Bank**: Struktur *Chart of Accounts* (Buku Besar) akuntansi.
- **Jenis Transaksi Bank**: Kategori transaksi beserta pemetaan default COA Debit/Kredit.
- **Pengaturan Bank**: Pengaturan umum kas limit harian, dll.

### 8. Manajemen Kamtib (`/dashboard/manajemen-kamtib`)
*Pengelolaan ketertiban, keamanan, dan kedisiplinan santri.*
- **Pelanggaran**: Pencatatan pelanggaran individu santri beserta bobot poin.
- **Kategori Pelanggaran**: Pengelompokan pelanggaran (ringan, sedang, berat).
- **Sanksi**: Jenis tindakan disiplin yang diberikan berdasarkan akumulasi poin.
- **Perizinan**: Pengajuan izin pulang (izin syar'i/sakit) bagi santri.
- **Tipe Izin**: Kategori izin (pulang, izin keluar lingkungan pesantren).
- **Laporan Kamtib**: Dokumen statistik kepatuhan santri.
- **Manajemen Libur**: Pengaturan tanggal libur massal/lebaran.
- **Verifikasi Libur**: Modul validasi kepulangan dan kedatangan kembali santri menggunakan scan barcode NIS.

### 9. Kepesantrenan (`/dashboard/kepesantrenan`)
*Mengatur asrama dan akomodasi fisik santri.*
- **Asrama**: Data bangunan gedung asrama.
- **Kamar**: Pengelolaan kamar di dalam asrama beserta kapasitas kasurnya.

### 10. Manajemen Pendidikan (`/dashboard/pendidikan`)
*Pengaturan administrasi sekolah.*
- **Program**: Program kepesantrenan (tahfidz, kitab kuning, bahasa).
- **Tahun Ajaran**: Tahun ajaran aktif beserta periode kuartal.
- **Jenjang**: Tingkat pendidikan formal (SMP, SMA, Madrasah).
- **Kelas**: Data fisik ruang kelas.
- **Rombel**: Rombongan belajar (gabungan jenjang dan kelas).
- **Kelompok Pendidikan**: Pengelompokan jenis peminatan/jurusan.
- **Jadwal Kegiatan**: Kalender aktivitas harian luar sekolah santri.

### 11. Informasi (`/dashboard/informasi`)
*Publikasi pengumuman internal dan berita eksternal.*
- **Berita**: Konten warta pesantren untuk konsumsi publik/wali santri.
- **Pengumuman**: Siaran pengumuman penting bagi wali dan staf.

### 12. Manajemen Santri (`/dashboard/santri`)
*Manajemen data induk kesiswaan.*
- **Data Santri**: Profil lengkap biodata santri aktif.
- **Pendaftaran Santri**: Modul Penerimaan Peserta Didik Baru (PPDB).
- **Wali Santri**: Data akun dan profil orang tua/wali santri.
- **Surat Perjanjian**: Unggah berkas dokumen perjanjian santri saat masuk.
- **Mutasi Asrama**: Fitur pemindahan santri antar kamar/asrama.
- **Penempatan Kelas**: Penugasan siswa ke rombongan belajar (rombel).

### 13. Data Master (`/dashboard/master-data`)
*Data master penunjang.*
- **Pekerjaan**: Referensi kategori pekerjaan orang tua.
- **Wilayah**: Manajemen bertingkat data Provinsi, Kota, Kecamatan, dan Desa.

### 14. Pengaturan Sistem (`/dashboard/settings`)
*Konfigurasi aplikasi.*
- **Navigasi**: Manajemen menu dinamis sidebar (menambah/merubah struktur menu).
- **Profil Aplikasi**: Nama instansi, logo, favicon, warna tema, dan gaya tampilan website.
- **Template Kartu Santri**: Pengaturan visual tata letak cetak kartu identitas santri (RFID/Barcode).
- **Peran & Izin**: Izin (*permission matrix*) untuk masing-masing Peran (`Role`).

### 15. Laporan Pesantren (`/dashboard/kesantrian/laporan`)
*Modul visualisasi data statistik bagi pimpinan pondok pesantren.*
- **Statistik Santri**: Grafik rasio gender, sebaran daerah asal, dan minat program.
- **Laporan Pelanggaran**: Grafik tren pelanggaran teratas.
- **Laporan Izin**: Statistik santri keluar-masuk per minggu.
- **Statistik Presensi**: Rasio kehadiran KBM santri.

---

## 3. Arsitektur & Database Bank Santri (`bank-santri`)

Aplikasi `bank-santri` dirancang menyerupai core banking skala mini dengan prinsip akuntansi syariah double-entry. SQLite database di `bank-santri/database/database.sqlite` menampung tabel-tabel utama berikut:

```text
┌──────────────────┐          ┌──────────────────┐          ┌──────────────────┐
│     products     │1 ────────│     accounts     │1 ────────│   top_up_requests│
│(Definisi Produk) │       *  │(Rekening Santri) │       *  │(Request Top-Up)  │
└──────────────────┘          └────────┬─────────┘          └──────────────────┘
                                       │ 1
                                       │
                                       │ *
                              ┌────────┴─────────┐
                              │   transactions   │
                              │(Transaksi Utama) │
                              └────────┬─────────┘
                                       │ 1
                         ┌─────────────┴─────────────┐
                         │ 1                         │ 1
                         │                           │
                         │ *                         │ *
              ┌──────────┴─────────┐      ┌──────────┴─────────┐
              │  account_movements │      │ transaction_ledgers│
              │ (Buku Pembantu Rek)│      │   (Jurnal COA)     │
              └────────────────────┘      └──────────┬─────────┘
                                                     │ *
                                                     │
                                                     │ 1
                                          ┌──────────┴─────────┐
                                          │ chart_of_accounts  │
                                          │   (Daftar COA)     │
                                          └────────────────────┘
```

- **`products`**: Definisi produk tabungan (contoh: *Tabungan Wadiah*, *Tabungan Mudharabah*) lengkap dengan biaya admin bulanan dan persentase nisbah bagi hasil.
- **`accounts`**: Rekening tabungan nasabah. Kolom utama `account_number` bertindak sebagai Primary Key dan nilainya **sama dengan NIS santri**. Saldo santri disimpan di kolom `balance`.
- **`transactions`**: Log riwayat transaksi keuangan. Menyimpan UUID transaksi, jenis transaksi, nomor rekening pengirim (`source_account`), nomor rekening penerima (`destination_account`), referensi pembayaran (misal dari Midtrans), jumlah uang (`amount`), status (`success`/`reversed`), dan akad syariah (`wadiah`/`murabahah`/`ijarah`).
- **`account_movements`**: Buku pembantu rekening nasabah. Setiap transaksi memicu pencatatan mutasi kredit/debit pada rekening terkait, dengan mendokumentasikan nilai saldo sebelum (`balance_before`) dan saldo sesudah (`balance_after`) transaksi untuk audit trail.
- **`transaction_ledgers`**: Jurnal umum akuntansi double-entry. Setiap satu transaksi akan dipecah menjadi minimal dua baris entri jurnal (sisi Debit dan sisi Kredit) yang wajib seimbang (*balanced*), memetakan uang masuk/keluar ke kode perkiraan di Chart of Accounts.
- **`chart_of_accounts`**: Daftar nomor akun perkiraan akuntansi (Asset, Liability, Equity, Revenue, Expense).
- **`top_up_requests`**: Log permintaan pengisian saldo tabungan oleh wali santri, baik melalui kasir tunai, transfer manual (unggah foto bukti bayar), maupun otomatis via Midtrans.

---

## 4. Alur Integrasi & Komunikasi Antar-Sistem

Sistem ini didesain agar saling terhubung secara erat, baik melalui komunikasi di belakang layar (Server-to-Server) maupun koordinasi di sisi client (Frontend API routing).

```text
           ┌───────────────────────────────────────────────┐
           │        React Frontend (smp-apps-fe)           │
           │                                               │
           │           ┌───────────────────────┐           │
           │           │   User Interface UI   │           │
           │           └───────────┬───────────┘           │
           │                       │                       │
           │           ┌───────────┴───────────┐           │
           │           │  Redux Store (State)  │           │
           │           └─────┬───────────┬─────┘           │
           │                 │           │                 │
           │        smpApi   │           │   bankSmpApi    │
           └─────────────────┼───────────┼─────────────────┘
                             │           │
             (HTTP requests) │           │ (HTTP requests)
                             ▼           ▼
           ┌───────────────────────┐   ┌───────────────────────┐
           │     Backend SMPT      │   │  Backend Bank Santri  │
           │      (Port 8000)      │   │      (Port 8001)      │
           │                       │   │                       │
           │  ┌─────────────────┐  │   │  ┌─────────────────┐  │
           │  │Laravel Controller│  │   │  │Laravel Controller│  │
           │  └────────┬────────┘  │   │  └────────┬────────┘  │
           │           │           │   │           │           │
           │  ┌────────▼────────┐  │   │  ┌────────▼────────┐  │
           │  │  SQLite DBSMPT  │  │   │  │   SQLite DBBank │  │
           │  └─────────────────┘  │   │  └─────────────────┘  │
           └───────────▲───────────┘   └───────────▲─────▲─────┘
                       │                           │     │
                       └───────────────────────────┘     │
                           Service-to-Service API        │ (Koperasi Key)
                           (X-Internal-Key Auth)         │
                                                         │
                                               ┌─────────┴─────────┐
                                               │   Koperasi API    │
                                               │ & Midtrans Webhook│
                                               └───────────────────┘
```

### A. Komunikasi Server-to-Server (Internal API)
Menggunakan otentikasi berbasis **header token rahasia** (`X-Internal-Key`) yang didefinisikan di `.env` kedua aplikasi, untuk menghindari overhead otentikasi JWT client:
1. **Aktivasi Santri Baru**: 
   - Saat registrasi PPDB disetujui setelah pembayaran dikonfirmasi di modul Bank Santri, `TransactionController` di SMPT mengirim request `POST` internal ke `http://localhost:8001/api/internal/account`.
   - Backend Bank Santri akan otomatis membuatkan rekening tabungan baru bagi santri tersebut menggunakan NIS-nya yang baru terbentuk.
2. **Sinkronisasi Data Profil & Auto-Provisioning**:
   - Di `AccountController.php` (Bank Santri), jika teller mencari nomor rekening santri yang belum terdaftar secara lokal di basis data Bank Santri (misalnya saat pembukaan rekening baru), controller secara otomatis mengirim panggilan HTTP `GET` ke `http://localhost:8000/api/main/student`.
   - Jika santri tersebut ditemukan di data akademik SMPT, Bank Santri akan **melakukan proses pembuatan rekening otomatis (*auto-provisioning*)**, sekaligus menembak `http://localhost:8000/api/main/student/card/{nis}` untuk mengambil data kartu RFID santri yang dikonfigurasi di sekolah agar disinkronisasikan sebagai nomor kartu debit tabungan.

### B. Integrasi Merchant & Koperasi Sekolah
- Toko atau koperasi pondok pesantren dapat didebit langsung menggunakan kartu santri.
- Sistem Koperasi (berjalan secara independen atau bagian eksternal) menembak API Bank Santri melalui grup rute `/api/koperasi/*`.
- Grup rute ini dilindungi oleh middleware `koperasi.key` yang membaca header `X-Koperasi-Key`.
- Rute yang tersedia meliputi:
  - `GET /api/koperasi/check/{nis}`: Memverifikasi keaktifan kartu santri dan sisa saldo tabungan nasabah.
  - `POST /api/koperasi/debit`: Mengurangi saldo santri secara instan ketika melakukan transaksi belanja, memicu mutasi kas dan penjurnalan akuntansi di sisi bank.

### C. Koordinasi di Sisi Client (Frontend Integration)
Frontend `smp-apps-fe` mengintegrasikan kedua sistem secara mulus melalui konfigurasi Redux:
- **Redux Slice Ganda**: Aplikasi mendefinisikan `smpApi` (untuk data sekolah) dan `bankSmpApi` (untuk tabungan & top-up).
- **Single Sign-On (SSO) Handover**: 
  - Token JWT hasil login disimpan secara terpusat di `localStorage`.
  - Kedua slice API menggunakan token JWT yang sama saat melakukan pemanggilan ke port 8000 dan 8001.
  - Ketika token kedaluwarsa, pembaruan token (*token refresh*) dikelola menggunakan mekanisme `Mutex` bersama. Mutex memastikan proses refresh token ke server hanya dijalankan sekali demi menjaga efisiensi kinerja jaringan dan mencegah logout paksa di salah satu modul.
