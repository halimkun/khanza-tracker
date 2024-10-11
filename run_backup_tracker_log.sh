#!/bin/bash

# Tahun yang ingin di-backup (misalnya 2023)
YEAR=2024

# Direktori tujuan backup
BACKUP_DIR="/backup/khanzaTracker"

# Direktori sumber log
LOG_DIR="/home/sysadmin/khanzaLog"

# Loop untuk setiap bulan (1 hingga 12)
for MONTH in {01..9}; do
    # Nama file log dan file tar.gz berdasarkan bulan dan tahun
    LOG_FILE="${LOG_DIR}/${YEAR}/${YEAR}-${MONTH}.log"
    TAR_FILE="${BACKUP_DIR}/${YEAR}-${MONTH}.tar.gz"
    
    # Cek apakah file log ada sebelum mengompres
    if [ -f "$LOG_FILE" ]; then
        echo "Compressing $LOG_FILE to $TAR_FILE"
        # Kompres file log menjadi tar.gz
        tar -czvf "$TAR_FILE" -C "${LOG_DIR}/${YEAR}" "${YEAR}-${MONTH}.log"
    else
        echo "Log file $LOG_FILE does not exist, skipping..."
    fi
done

echo "Backup process completed."
