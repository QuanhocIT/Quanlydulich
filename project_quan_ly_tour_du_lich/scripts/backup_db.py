#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Database Backup Script (Python)
Thay thế cho backup_db.php

Chạy:
    python scripts/backup_db.py

Để chạy hàng ngày lúc 2:00 sáng, thêm vào cron:
    0 2 * * * cd /path/to/project && python scripts/backup_db.py
"""

import gzip
import logging
import shutil
import subprocess
import sys
import time
from datetime import datetime, timedelta
from pathlib import Path
from typing import Optional, Tuple

from commons.config_helper import load_config

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s'
)
logger = logging.getLogger(__name__)

# Load config
config = load_config()

DB_HOST = config.get('DB_HOST', 'localhost')
DB_PORT = int(config.get('DB_PORT', 3306))
DB_NAME = config.get('DB_NAME', 'quan_ly_tour_du_lich')
DB_USER = config.get('DB_USER', 'root')
DB_PASSWORD = config.get('DB_PASSWORD', '')

# Configuration
BACKUP_DIR = Path(__file__).parent.parent / 'storage' / 'backups'
RETENTION_DAYS = 14  # Keep backups for 14 days


def detect_mysqldump() -> Optional[str]:
    """Find mysqldump binary."""
    candidates = [
        r'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe',
        r'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
        '/usr/bin/mysqldump',
        '/usr/local/bin/mysqldump',
        'mysqldump',  # From PATH
    ]
    
    for path in candidates:
        try:
            # Check if executable
            if shutil.which(path):
                return path
            elif Path(path).exists():
                return path
        except Exception:
            pass
    
    return None


def create_backup() -> Tuple[bool, str, float]:
    """
    Create database backup.
    
    Returns:
        Tuple of (success, filename, size_mb)
    """
    # Ensure backup directory exists
    BACKUP_DIR.mkdir(parents=True, exist_ok=True)

    # Generate filename
    timestamp = datetime.now().strftime('%Y-%m-%d_%H-%M-%S')
    sql_filename = f'{DB_NAME}_{timestamp}.sql'
    backup_filename = f'{sql_filename}.gz'
    backup_path = BACKUP_DIR / backup_filename

    # Find mysqldump
    mysqldump_bin = detect_mysqldump()
    if not mysqldump_bin:
        logger.error('Cannot find mysqldump binary')
        return False, '', 0

    logger.info(f'Starting backup of {DB_NAME}...')

    try:
        # Build mysqldump command
        cmd = [
            mysqldump_bin,
            f'--host={DB_HOST}',
            f'--port={DB_PORT}',
            f'--user={DB_USER}',
            '--single-transaction',
            '--routines',
            '--triggers',
            '--set-gtid-purged=OFF',
        ]

        # Add password if provided
        if DB_PASSWORD:
            cmd.append(f'--password={DB_PASSWORD}')

        cmd.append(DB_NAME)

        # Execute mysqldump and pipe to gzip
        logger.info(f'Executing: {" ".join([c if " " not in c else f\'"{c}\'' for c in cmd[:5]])} ...')
        
        with open(backup_path, 'wb') as backup_file:
            # Use stderr=subprocess.PIPE to capture errors
            process = subprocess.Popen(
                cmd,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
            )
            
            # Compress on-the-fly
            with gzip.open(backup_file, 'wb') as gz:
                while True:
                    chunk = process.stdout.read(65536)  # 64KB chunks
                    if not chunk:
                        break
                    gz.write(chunk)
            
            process.wait()
            
            if process.returncode != 0:
                error_msg = process.stderr.read().decode('utf-8', errors='replace')
                logger.error(f'mysqldump failed: {error_msg}')
                if backup_path.exists():
                    backup_path.unlink()
                return False, '', 0

        # Verify backup
        if not backup_path.exists() or backup_path.stat().st_size < 100:
            logger.error('Backup file is too small or missing')
            if backup_path.exists():
                backup_path.unlink()
            return False, '', 0

        size_mb = round(backup_path.stat().st_size / 1048576, 2)
        logger.info(f'OK — {backup_filename} ({size_mb} MB)')
        return True, backup_filename, size_mb

    except Exception as e:
        logger.error(f'Backup failed: {e}')
        if backup_path.exists():
            try:
                backup_path.unlink()
            except Exception:
                pass
        return False, '', 0


def cleanup_old_backups() -> int:
    """
    Delete backups older than RETENTION_DAYS.
    
    Returns:
        Number of deleted files
    """
    if not BACKUP_DIR.exists():
        return 0

    cutoff_time = time.time() - (RETENTION_DAYS * 86400)
    deleted = 0

    for backup_file in BACKUP_DIR.glob('*.sql.gz'):
        try:
            file_mtime = backup_file.stat().st_mtime
            if file_mtime < cutoff_time:
                backup_file.unlink()
                deleted += 1
                logger.info(f'Deleted old backup: {backup_file.name}')
        except Exception as e:
            logger.warning(f'Failed to delete {backup_file.name}: {e}')

    if deleted > 0:
        logger.info(
            f'Deleted {deleted} backup(s) older than {RETENTION_DAYS} days'
        )

    return deleted


def main():
    """Main entry point."""
    logger.info('Database backup started')

    # Create backup
    success, filename, size_mb = create_backup()
    if not success:
        logger.error('Backup failed')
        sys.exit(1)

    logger.info(f'Backup created: {filename} ({size_mb} MB)')

    # Cleanup old backups
    deleted = cleanup_old_backups()
    if deleted > 0:
        logger.info(f'Cleaned up {deleted} old backup(s)')

    logger.info('Backup completed successfully')
    sys.exit(0)


if __name__ == '__main__':
    main()
