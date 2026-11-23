#!/bin/bash
# ==============================================================================
# SCRIPT BACKUP MULTI-DATABASE OTOMATIS KE GOOGLE DRIVE (RCLONE)
# Ekosistem Pesantren: api-tamjid, tarbiyah, bank-santri
# ==============================================================================

set -o pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${SCRIPT_DIR}/backup.conf"

# Cek file konfigurasi
if [ -f "$CONFIG_FILE" ]; then
    # shellcheck disable=SC1090
    source "$CONFIG_FILE"
elif [ -f "/etc/pesantren/backup.conf" ]; then
    # shellcheck disable=SC1091
    source "/etc/pesantren/backup.conf"
else
    echo "[ERROR] File konfigurasi backup.conf tidak ditemukan!"
    echo "Salin backup.conf.example menjadi backup.conf terlebih dahulu."
    exit 1
fi

TIMESTAMP=$(date +"%Y-%m-%d_%H%M%S")
DATE_DIR=$(date +"%Y-%m")
RUN_DIR="${LOCAL_TEMP_DIR}/${TIMESTAMP}"
LOG_FILE="${LOG_FILE:-/tmp/backup-pesantren.log}"

# Fungsi pencatatan log
log() {
    local MSG="[$(date +"%Y-%m-%d %H:%M:%S")] $1"
    echo "$MSG"
    mkdir -p "$(dirname "$LOG_FILE")" 2>/dev/null || true
    echo "$MSG" >> "$LOG_FILE" 2>/dev/null || true
}

# Fungsi notifikasi Telegram
send_telegram() {
    if [ "$ENABLE_TELEGRAM" = "true" ] && [ -n "$TELEGRAM_BOT_TOKEN" ] && [ -n "$TELEGRAM_CHAT_ID" ]; then
        local TEXT="$1"
        curl -s -X POST "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
            -d "chat_id=${TELEGRAM_CHAT_ID}" \
            -d "text=${TEXT}" \
            -d "parse_mode=Markdown" > /dev/null 2>&1 || true
    fi
}

log "========================================================"
log "MEMULAI PROSES BACKUP OTOMATIS MULTI-DATABASE"
log "========================================================"

# 1. Validasi binary yang dibutuhkan
for cmd in mysqldump gzip rclone; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
        log "[ERROR] Command '$cmd' tidak ditemukan. Harap install terlebih dahulu."
        send_telegram "❌ *Gagal Backup Pesantren*: Command \`$cmd\` tidak ditemukan di server."
        exit 1
    fi
done

# 2. Siapkan folder temporer
mkdir -p "$RUN_DIR"
if [ ! -d "$RUN_DIR" ]; then
    log "[ERROR] Gagal membuat direktori lokal temporer: $RUN_DIR"
    exit 1
fi

SUCCESS_COUNT=0
FAIL_COUNT=0

# 3. Eksekusi Dump Database
# Opsi 3A: Backup masing-masing database per file
for DB_NAME in "${DATABASES[@]}"; do
    TARGET_FILE="${RUN_DIR}/${DB_NAME}_${TIMESTAMP}.sql.gz"
    log "Dumping database: [${DB_NAME}] ..."
    
    if mysqldump -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" \
        --single-transaction --quick --routines --triggers --databases "${DB_NAME}" 2>/dev/null | gzip > "${TARGET_FILE}"; then
        
        FILE_SIZE=$(du -h "${TARGET_FILE}" | cut -f1)
        log "Berhasil dump [${DB_NAME}] -> ${TARGET_FILE} (${FILE_SIZE})"
        ((SUCCESS_COUNT++))
    else
        log "[WARNING] Gagal dump database [${DB_NAME}]! Cek izin akses user/database."
        ((FAIL_COUNT++))
    fi
done

# Opsi 3B: Backup seluruh database sekaligus jika diaktifkan
if [ "$BACKUP_ALL_DATABASES" = "true" ]; then
    ALL_DB_FILE="${RUN_DIR}/all_databases_master_${TIMESTAMP}.sql.gz"
    log "Dumping seluruh database (--all-databases) ..."
    if mysqldump -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" \
        --single-transaction --quick --routines --triggers --all-databases 2>/dev/null | gzip > "${ALL_DB_FILE}"; then
        
        FILE_SIZE=$(du -h "${ALL_DB_FILE}" | cut -f1)
        log "Berhasil dump Master All DB -> ${ALL_DB_FILE} (${FILE_SIZE})"
    else
        log "[WARNING] Gagal dump --all-databases."
    fi
fi

# 4. Upload ke Google Drive via Rclone
REMOTE_TARGET="${RCLONE_REMOTE}:${RCLONE_DEST_DIR}/${DATE_DIR}"
log "Mengunggah file backup ke Google Drive: ${REMOTE_TARGET} ..."

if rclone copy "${RUN_DIR}" "${REMOTE_TARGET}" --fast-list --transfers=2 2>> "$LOG_FILE"; then
    log "Berhasil mengunggah semua file backup ke Google Drive!"
    UPLOAD_STATUS="SUCCESS"
else
    log "[ERROR] Gagal mengunggah file backup ke Google Drive via rclone."
    UPLOAD_STATUS="FAILED"
fi

# 5. Bersihkan file lokal di direktori temporer
rm -rf "$RUN_DIR"
log "Pembersihan direktori temporer lokal selesai."

# 6. Jalankan Retensi (Hapus backup lama di Google Drive)
if [ -n "$RETENTION_DAYS" ] && [ "$UPLOAD_STATUS" = "SUCCESS" ]; then
    log "Menjalankan retensi: Menghapus file backup di Google Drive yang lebih tua dari ${RETENTION_DAYS} ..."
    rclone delete --min-age "${RETENTION_DAYS}" "${RCLONE_REMOTE}:${RCLONE_DEST_DIR}/" 2>> "$LOG_FILE" || true
    rclone rmdirs --leave-root "${RCLONE_REMOTE}:${RCLONE_DEST_DIR}/" 2>> "$LOG_FILE" || true
    log "Pembersihan retensi Google Drive selesai."
fi

# 7. Laporan Selesai & Notifikasi Telegram
log "========================================================"
if [ "$UPLOAD_STATUS" = "SUCCESS" ] && [ "$FAIL_COUNT" -eq 0 ]; then
    log "PROSES BACKUP SUKSES PENUH ($SUCCESS_COUNT database)."
    send_telegram "✅ *Backup Multi-Database Pesantren Berhasil*
📅 Waktu: \`${TIMESTAMP}\`
📁 Tujuan: Google Drive (\`${RCLONE_DEST_DIR}/${DATE_DIR}\`)
🗄️ Database: \`${DATABASES[*]}\`"
else
    log "PROSES BACKUP SELESAI DENGAN CATATAN (Gagal: $FAIL_COUNT database, Upload: $UPLOAD_STATUS)."
    send_telegram "⚠️ *Backup Multi-Database Selesai dengan Catatan*
📅 Waktu: \`${TIMESTAMP}\`
Status Upload: \`${UPLOAD_STATUS}\`
Jumlah Gagal Dump: \`${FAIL_COUNT}\`"
fi
log "========================================================"
