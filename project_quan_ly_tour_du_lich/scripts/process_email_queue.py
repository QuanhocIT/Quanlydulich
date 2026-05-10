#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Email Queue Processor (Python)
Thay thế cho process_email_queue.php

Chạy:
    python scripts/process_email_queue.py

Để chạy mỗi 1 phút, thêm vào cron:
    * * * * * cd /path/to/project && python scripts/process_email_queue.py
"""

import logging
import smtplib
import sys
import time
from datetime import datetime, timedelta
from email.mime.base import MIMEBase
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.encoders import encode_base64
from html.parser import HTMLParser
from pathlib import Path
from typing import Dict, Optional, Tuple

from commons.db_helper import get_db_connection, close_db_connection
from commons.config_helper import load_config

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s'
)
logger = logging.getLogger(__name__)

# Load config
config = load_config()

MAIL_ENABLED = config.get('MAIL_ENABLED', '1') != '0'
SMTP_HOST = config.get('SMTP_HOST', '')
SMTP_PORT = int(config.get('SMTP_PORT', 587))
SMTP_USERNAME = config.get('SMTP_USERNAME', '')
SMTP_PASSWORD = config.get('SMTP_PASSWORD', '')
SMTP_AUTH = config.get('SMTP_AUTH', '1') != '0'
SMTP_SECURITY = config.get('SMTP_SECURITY', 'tls')  # 'tls' or 'ssl'
SMTP_TIMEOUT = int(config.get('SMTP_TIMEOUT', 30))
MAIL_FROM_ADDRESS = config.get('MAIL_FROM_ADDRESS', 'noreply@aventuratours.vn')
MAIL_FROM_NAME = config.get('MAIL_FROM_NAME', 'Aventura Tours')


class HtmlToText(HTMLParser):
    """Convert HTML to plain text."""

    def __init__(self):
        super().__init__()
        self.text = []

    def handle_data(self, data):
        self.text.append(data)

    def get_text(self):
        return ''.join(self.text).strip()


def build_plain_text_from_html(html: str) -> str:
    """Convert HTML body to plain text."""
    try:
        parser = HtmlToText()
        parser.feed(html)
        return parser.get_text()
    except Exception:
        return html


def normalize_mail_address(email: str) -> str:
    """Normalize and validate email address."""
    email = str(email).strip().lower()
    if not email or '@' not in email:
        return ''
    return email


def send_html_email(
    to: str,
    subject: str,
    html_body: str,
    text_body: str = '',
    attachment_path: Optional[str] = None,
    attachment_name: str = ''
) -> bool:
    """
    Send HTML email via SMTP.
    
    Returns:
        True if sent successfully, False otherwise
    """
    to = normalize_mail_address(to)
    subject = str(subject).strip()

    if not to or not subject:
        logger.warning(f'Invalid mail request: to={to}, subject={subject}')
        return False

    if not MAIL_ENABLED:
        logger.warning(f'Mail disabled, not sending to {to}')
        return False

    if not SMTP_HOST:
        logger.error('SMTP_HOST not configured')
        return False

    try:
        # Create message
        msg = MIMEMultipart('alternative')
        msg['From'] = f'{MAIL_FROM_NAME} <{MAIL_FROM_ADDRESS}>'
        msg['To'] = to
        msg['Subject'] = subject
        msg['Date'] = datetime.now().strftime('%a, %d %b %Y %H:%M:%S %z')

        # Add text and HTML parts
        if not text_body:
            text_body = build_plain_text_from_html(html_body)

        msg.attach(MIMEText(text_body, 'plain', 'utf-8'))
        msg.attach(MIMEText(html_body, 'html', 'utf-8'))

        # Add attachment if provided
        if attachment_path:
            path = Path(attachment_path)
            if path.exists() and path.is_file():
                if not attachment_name:
                    attachment_name = path.name
                
                try:
                    with open(attachment_path, 'rb') as f:
                        part = MIMEBase('application', 'octet-stream')
                        part.set_payload(f.read())
                        encode_base64(part)
                        part.add_header(
                            'Content-Disposition',
                            f'attachment; filename= {attachment_name}'
                        )
                        msg.attach(part)
                except Exception as e:
                    logger.warning(f'Failed to attach file {attachment_path}: {e}')

        # Send email
        with smtplib.SMTP(SMTP_HOST, SMTP_PORT, timeout=SMTP_TIMEOUT) as server:
            if SMTP_SECURITY.lower() == 'tls':
                server.starttls()
            elif SMTP_SECURITY.lower() == 'ssl':
                server.quit()
                server = smtplib.SMTP_SSL(
                    SMTP_HOST, SMTP_PORT, timeout=SMTP_TIMEOUT
                )
            
            if SMTP_AUTH:
                server.login(SMTP_USERNAME, SMTP_PASSWORD)
            
            server.send_message(msg)

        logger.info(f'Email sent to {to} (subject: {subject[:50]})')
        return True

    except Exception as e:
        logger.error(f'Failed to send email to {to}: {e}')
        return False


def backoff_minutes(attempt: int) -> int:
    """Calculate exponential backoff in minutes."""
    if attempt <= 1:
        return 2
    elif attempt == 2:
        return 10
    else:
        return 30


class EmailQueueProcessor:
    """Process email queue from database."""

    def __init__(self):
        self.conn = get_db_connection()
        if not self.conn:
            raise Exception('Failed to connect to database')

    def process_queue(self, batch_size: int = 20) -> Tuple[int, int]:
        """
        Process pending emails in queue.
        
        Returns:
            Tuple of (sent, failed) counts
        """
        if not self.conn:
            return 0, 0

        sent = 0
        failed = 0

        try:
            cursor = self.conn.cursor(dictionary=True, buffered=True)

            # Get pending emails
            cursor.execute('''
                SELECT id, to_email, subject, body_html, attempts, max_attempts
                FROM email_queue
                WHERE status = 'pending'
                  AND attempts < max_attempts
                  AND scheduled_at <= NOW()
                ORDER BY id ASC
                LIMIT %s
            ''', (batch_size,))

            rows = cursor.fetchall()
            if not rows:
                cursor.close()
                return 0, 0

            # Mark as processing
            ids = [row['id'] for row in rows]
            id_placeholders = ','.join(['%s'] * len(ids))
            cursor.execute(
                f'UPDATE email_queue SET status = "processing" WHERE id IN ({id_placeholders})',
                ids
            )
            self.conn.commit()
            cursor.close()

            # Send each email
            for row in rows:
                email_id = row['id']
                to_email = row['to_email']
                subject = row['subject']
                body_html = row['body_html']
                attempts = int(row['attempts']) + 1
                max_attempts = int(row['max_attempts'])

                try:
                    if send_html_email(to_email, subject, body_html):
                        # Mark as sent
                        cursor = self.conn.cursor()
                        cursor.execute(
                            'UPDATE email_queue '
                            'SET status = "sent", attempts = %s, sent_at = NOW(), last_error = NULL '
                            'WHERE id = %s',
                            (attempts, email_id)
                        )
                        self.conn.commit()
                        cursor.close()
                        sent += 1
                    else:
                        # Mark as failed or retry
                        self._mark_email_failed(
                            email_id, attempts, max_attempts, 'SMTP send failed'
                        )
                        failed += 1

                except Exception as e:
                    self._mark_email_failed(
                        email_id, attempts, max_attempts, str(e)[:500]
                    )
                    failed += 1
                    logger.error(f'Email {email_id} attempt {attempts}: {e}')

        except Exception as e:
            logger.error(f'Queue processing error: {e}')

        return sent, failed

    def _mark_email_failed(
        self, email_id: int, attempts: int, max_attempts: int, error_msg: str
    ) -> None:
        """Mark email as failed or schedule for retry."""
        if not self.conn:
            return

        try:
            cursor = self.conn.cursor()
            
            if attempts >= max_attempts:
                new_status = 'failed'
                scheduled_at = None
            else:
                new_status = 'pending'
                backoff = backoff_minutes(attempts)
                scheduled_at = f'DATE_ADD(NOW(), INTERVAL {backoff} MINUTE)'

            if scheduled_at:
                cursor.execute(
                    'UPDATE email_queue '
                    'SET status = %s, attempts = %s, last_error = %s, scheduled_at = ' + scheduled_at + ' '
                    'WHERE id = %s',
                    (new_status, attempts, error_msg, email_id)
                )
            else:
                cursor.execute(
                    'UPDATE email_queue '
                    'SET status = %s, attempts = %s, last_error = %s '
                    'WHERE id = %s',
                    (new_status, attempts, error_msg, email_id)
                )
            
            self.conn.commit()
            cursor.close()
        except Exception as e:
            logger.error(f'Error marking email {email_id} as failed: {e}')

    def enqueue(
        self, to_email: str, subject: str, body_html: str, max_attempts: int = 3
    ) -> int:
        """
        Add email to queue.
        
        Returns:
            Queue ID if successful, 0 otherwise
        """
        to_email = normalize_mail_address(to_email)
        if not to_email:
            logger.warning(f'Invalid email: {to_email}')
            return 0

        try:
            cursor = self.conn.cursor()
            cursor.execute(
                'INSERT INTO email_queue '
                '(to_email, subject, body_html, max_attempts, status, scheduled_at, created_at) '
                'VALUES (%s, %s, %s, %s, "pending", NOW(), NOW())',
                (to_email, subject, body_html, max(1, max_attempts))
            )
            self.conn.commit()
            queue_id = cursor.lastrowid
            cursor.close()
            return queue_id
        except Exception as e:
            logger.error(f'Error enqueuing email: {e}')
            return 0


def main():
    """Main entry point."""
    lock_file = Path('/tmp/aventura_email_queue.lock')
    
    # Simple file lock to prevent concurrent runs
    try:
        if lock_file.exists():
            # Check if lock is stale (older than 5 minutes)
            age = time.time() - lock_file.stat().st_mtime
            if age < 300:
                logger.info('Another instance is running. Skipping.')
                return
        
        # Create lock file
        lock_file.touch()
        
        start_time = time.time()
        logger.info('Starting email queue processing...')
        
        processor = EmailQueueProcessor()
        sent, failed = processor.process_queue(batch_size=20)
        
        elapsed = round(time.time() - start_time, 3)
        logger.info(
            f'Done: sent={sent}, failed={failed}, time={elapsed}s'
        )
        
        close_db_connection(processor.conn)
        
    except Exception as e:
        logger.error(f'Fatal error: {e}')
    finally:
        # Clean up lock file
        try:
            lock_file.unlink()
        except Exception:
            pass


if __name__ == '__main__':
    main()
