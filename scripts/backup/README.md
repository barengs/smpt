# Panduan Setup Backup Otomatis Multi-Database ke Google Drive

Panduan ini digunakan untuk mengaktifkan backup otomatis terjadwal seluruh database MySQL (`pesantren`, `pendidikan`, `bank`, dll) langsung ke **Google Drive** menggunakan tool **`rclone`**.

---

## 1. Persiapan di Server Linux (VPS)

### A. Install Rclone
Jalankan perintah ini di terminal server Anda:
```bash
sudo -v ; curl https://rclone.org/install.sh | sudo bash
```
Pastikan `rclone` sudah terpasang:
```bash
rclone version
```

### B. Hubungkan Rclone ke Akun Google Drive (Hanya 1x Setup)
Jalankan wizard konfigurasi rclone:
```bash
rclone config
```

Ikuti langkah interaktif berikut:
1. Ketik `n` (New remote).
2. Beri nama: **`gdrive`** *(harus sama dengan `RCLONE_REMOTE` di `backup.conf`)*.
3. Pilih storage type: ketik **`drive`** (atau pilih nomor untuk Google Drive).
4. `client_id`: kosongkan (tekan Enter).
5. `client_secret`: kosongkan (tekan Enter).
6. `scope`: pilih **`1`** (Full access all files).
7. `root_folder_id`: kosongkan (tekan Enter).
8. `service_account_file`: kosongkan (tekan Enter).
9. `Edit advanced config`: ketik **`n`**.
10. `Use web browser to automatically authenticate`:
    - Jika server memiliki GUI/browser: ketik **`y`**.
    - Jika server remote (SSH): ketik **`n`**, rclone akan menampilkan URL atau perintah token. Anda cukup buka URL tersebut di browser laptop Anda, login ke Google, izinkan akses, lalu salin kode verifikasi ke terminal.
11. `Configure this as a Shared Drive (Team Drive)`: ketik **`n`**.
12. `Keep this "gdrive" remote?`: ketik **`y`**.
13. Ketik **`q`** untuk keluar dari wizard.

**Tes koneksi:**
```bash
rclone lsd gdrive:
```
Jika daftar folder di Google Drive Anda muncul, koneksi sudah berhasil!

---

## 2. Konfigurasi Script Backup

1. Masuk ke direktori script:
   ```bash
   cd /path/ke/api-tamjid/scripts/backup
   ```
2. Buat file `backup.conf` dari template:
   ```bash
   cp backup.conf.example backup.conf
   chmod 600 backup.conf
   chmod +x backup-all-databases.sh
   ```
3. Edit `backup.conf`:
   ```bash
   nano backup.conf
   ```
   Sesuaikan:
   - `DB_USER` dan `DB_PASS`: Kredensial MySQL server Anda.
   - `DATABASES`: Daftar nama database yang ingin dibackup, misalnya:
     `DATABASES=("pesantren" "pendidikan" "bank")`
   - `RCLONE_DEST_DIR`: Nama folder di Google Drive (default: `Backup-Pesantren/Databases`).
   - `RETENTION_DAYS`: Masa simpan (default `30d` = otomatis hapus file yang berumur lebih dari 30 hari).
   - `ENABLE_TELEGRAM`: Set `true` dan isi token bot jika ingin notifikasi ke Telegram.

4. **Uji coba manual sekali**:
   ```bash
   ./backup-all-databases.sh
   ```
   Cek Google Drive Anda, folder `Backup-Pesantren/Databases/YYYY-MM/` akan terisi file `.sql.gz`.

---

## 3. Menjadwalkan Otomatisasi (Cronjob)

Buka crontab server:
```bash
crontab -e
```

Tambahkan jadwal eksekusi di baris paling bawah.

### Pilihan Jadwal:
- **Tiap hari jam 02:00 dini hari (Sangat disarankan)**:
  ```cron
  0 2 * * * /path/ke/api-tamjid/scripts/backup/backup-all-databases.sh >> /var/log/backup-pesantren.log 2>&1
  ```
- **Tiap 6 jam sekali (jam 00:00, 06:00, 12:00, 18:00)**:
  ```cron
  0 */6 * * * /path/ke/api-tamjid/scripts/backup/backup-all-databases.sh >> /var/log/backup-pesantren.log 2>&1
  ```
- **Tiap 12 jam sekali (jam 02:00 dan jam 14:00)**:
  ```cron
  0 2,14 * * * /path/ke/api-tamjid/scripts/backup/backup-all-databases.sh >> /var/log/backup-pesantren.log 2>&1
  ```

---

## 4. Lokasi Log & Pemantauan
Untuk melihat riwayat log proses backup:
```bash
tail -n 50 -f /var/log/backup-pesantren.log
```
