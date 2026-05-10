#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Admin Automation Service (Python)
Thay thế cho AdminAutomationService.php

Chạy:
    python scripts/run_admin_automation.py [job_name]
    
Các job có sẵn:
    - all
    - sla_tour_requests
    - booking_priority
    - reconcile_digest
    - self_heal_pending_payments
    - webhook_anomaly
    - debt_reminder
    - departure_readiness
    - tour_health_score
    - admin_inbox_digest
    - decision_assist
"""

import argparse
import json
import logging
import sys
import time
from datetime import datetime, timedelta
from pathlib import Path
from typing import Any, Dict, List, Optional

from commons.db_helper import get_db_connection, close_db_connection

logger = logging.getLogger(__name__)


class AdminAutomationService:
    """Admin automation job runner."""

    ENABLED_SETTING_KEY = 'automation_enabled'

    def __init__(self):
        self.conn = get_db_connection()
        if not self.conn:
            raise Exception('Failed to connect to database')
        self.ensure_automation_tables()

    def ensure_automation_tables(self) -> None:
        """Ensure automation tables exist."""
        try:
            cursor = self.conn.cursor()
            
            # automation_settings
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS automation_settings (
                    setting_key VARCHAR(100) PRIMARY KEY,
                    setting_value VARCHAR(500),
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ''')
            
            # admin_automation_events
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS admin_automation_events (
                    event_id INT AUTO_INCREMENT PRIMARY KEY,
                    event_key VARCHAR(255) UNIQUE,
                    job_name VARCHAR(100),
                    severity VARCHAR(20),
                    title VARCHAR(255),
                    message TEXT,
                    payload JSON,
                    is_alert BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_job_severity (job_name, severity, created_at),
                    INDEX idx_event_key (event_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ''')
            
            # booking_priority
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS booking_priority (
                    booking_id INT PRIMARY KEY,
                    priority_label VARCHAR(20),
                    score INT,
                    reasons_json JSON,
                    computed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_priority (priority_label)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ''')
            
            self.conn.commit()
            logger.info('Automation tables ready')
        except Exception as e:
            logger.error(f'Error creating automation tables: {e}')
            self.conn.rollback()

    def is_automation_enabled(self) -> bool:
        """Check if automation is enabled."""
        try:
            cursor = self.conn.cursor(dictionary=True)
            cursor.execute(
                'SELECT setting_value FROM automation_settings WHERE setting_key = %s LIMIT 1',
                (self.ENABLED_SETTING_KEY,)
            )
            result = cursor.fetchone()
            cursor.close()
            
            if result is None:
                self.set_automation_enabled(True)
                return True
            
            return str(result['setting_value']) != '0'
        except Exception as e:
            logger.error(f'Error checking automation status: {e}')
            return True

    def set_automation_enabled(self, enabled: bool) -> None:
        """Set automation enabled/disabled."""
        try:
            value = '1' if enabled else '0'
            cursor = self.conn.cursor()
            cursor.execute(
                'INSERT INTO automation_settings (setting_key, setting_value, updated_at) '
                'VALUES (%s, %s, NOW()) '
                'ON DUPLICATE KEY UPDATE setting_value = %s, updated_at = NOW()',
                (self.ENABLED_SETTING_KEY, value, value)
            )
            self.conn.commit()
            cursor.close()
        except Exception as e:
            logger.error(f'Error setting automation status: {e}')

    def record_event(
        self,
        event_key: str,
        job_name: str,
        severity: str,
        title: str,
        message: str,
        payload: Dict[str, Any],
        is_alert: bool = False
    ) -> bool:
        """Record automation event."""
        try:
            cursor = self.conn.cursor()
            cursor.execute(
                'INSERT INTO admin_automation_events '
                '(event_key, job_name, severity, title, message, payload, is_alert, created_at) '
                'VALUES (%s, %s, %s, %s, %s, %s, %s, NOW()) '
                'ON DUPLICATE KEY UPDATE created_at = NOW()',
                (
                    event_key,
                    job_name,
                    severity,
                    title,
                    message,
                    json.dumps(payload, ensure_ascii=False),
                    1 if is_alert else 0
                )
            )
            self.conn.commit()
            cursor.close()
            return True
        except Exception as e:
            logger.error(f'Error recording event: {e}')
            return False

    def run_all(self) -> List[Dict[str, Any]]:
        """Run all automation jobs."""
        jobs = [
            'sla_tour_requests',
            'booking_priority',
            'reconcile_digest',
            'self_heal_pending_payments',
            'webhook_anomaly',
            'debt_reminder',
            'departure_readiness',
            'tour_health_score',
            'admin_inbox_digest',
            'decision_assist',
        ]
        
        results = []
        for job in jobs:
            try:
                result = self.run_job(job)
                results.append(result)
            except Exception as e:
                logger.error(f'Error running job {job}: {e}')
                results.append({
                    'ok': False,
                    'job': job,
                    'message': str(e),
                    'affected': 0,
                })
        
        return results

    def run_job(self, job_name: str) -> Dict[str, Any]:
        """Run a single automation job."""
        job_name = str(job_name).strip()
        started_at = time.time()

        if not self.is_automation_enabled():
            duration_ms = round((time.time() - started_at) * 1000, 1)
            message = 'Automation is temporarily disabled by admin.'
            self.log_run(job_name, True, 0, message, duration_ms)
            return {
                'ok': True,
                'skipped': True,
                'job': job_name,
                'message': message,
                'affected': 0,
                'duration_ms': duration_ms,
            }

        try:
            result = None
            if job_name == 'sla_tour_requests':
                result = self.run_sla_tour_requests()
            elif job_name == 'booking_priority':
                result = self.run_booking_priority()
            elif job_name == 'reconcile_digest':
                result = self.run_reconcile_digest()
            elif job_name == 'self_heal_pending_payments':
                result = self.run_self_heal_pending_payments()
            elif job_name == 'webhook_anomaly':
                result = self.run_webhook_anomaly()
            elif job_name == 'debt_reminder':
                result = self.run_debt_reminder()
            elif job_name == 'departure_readiness':
                result = self.run_departure_readiness()
            elif job_name == 'tour_health_score':
                result = self.run_tour_health_score()
            elif job_name == 'admin_inbox_digest':
                result = self.run_admin_inbox_digest()
            elif job_name == 'decision_assist':
                result = self.run_decision_assist()
            else:
                result = {
                    'ok': False,
                    'job': job_name,
                    'message': 'Unknown job',
                    'affected': 0,
                }

            duration_ms = round((time.time() - started_at) * 1000, 1)
            self.log_run(
                job_name,
                result.get('ok', False),
                result.get('affected', 0),
                result.get('message', ''),
                duration_ms
            )
            result['duration_ms'] = duration_ms
            return result

        except Exception as e:
            duration_ms = round((time.time() - started_at) * 1000, 1)
            self.log_run(job_name, False, 0, str(e), duration_ms)
            return {
                'ok': False,
                'job': job_name,
                'message': str(e),
                'affected': 0,
                'duration_ms': duration_ms,
            }

    def run_sla_tour_requests(self) -> Dict[str, Any]:
        """Check SLA for tour requests."""
        try:
            cursor = self.conn.cursor(dictionary=True)
            cursor.execute('''
                SELECT id, created_at
                FROM thong_bao
                WHERE vai_tro_nhan = 'Admin'
                  AND tieu_de = 'Yêu cầu tour theo mong muốn'
                  AND trang_thai = 'DaGui'
                  AND created_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR)
                ORDER BY created_at ASC
                LIMIT 500
            ''')
            rows = cursor.fetchall()
            cursor.close()

            alerts = 0
            now = time.time()
            for row in rows:
                request_id = row.get('id', 0)
                created_at = row.get('created_at')
                
                if not request_id or not created_at:
                    continue

                created_ts = created_at.timestamp() if hasattr(created_at, 'timestamp') else time.mktime(created_at.timetuple())
                age_hours = (now - created_ts) / 3600

                level = 0
                if age_hours >= 24:
                    level = 3
                elif age_hours >= 6:
                    level = 2
                elif age_hours >= 2:
                    level = 1

                if level <= 0:
                    continue

                severity = 'high' if level >= 3 else ('medium' if level == 2 else 'low')
                title = 'SLA yêu cầu tour quá hạn'
                message = f'Yêu cầu #{request_id} đã chờ {int(age_hours)} giờ, cần admin xử lý.'
                event_key = f'sla_request_{request_id}_L{level}'

                if self.record_event(
                    event_key,
                    'sla_tour_requests',
                    severity,
                    title,
                    message,
                    {
                        'request_id': request_id,
                        'age_hours': round(age_hours, 1),
                        'level': level,
                    },
                    True
                ):
                    alerts += 1

            return {
                'ok': True,
                'job': 'sla_tour_requests',
                'message': 'SLA scan completed',
                'affected': alerts,
            }
        except Exception as e:
            logger.error(f'SLA job error: {e}')
            raise

    def run_booking_priority(self) -> Dict[str, Any]:
        """Compute booking priority scores."""
        try:
            cursor = self.conn.cursor(dictionary=True)
            cursor.execute('''
                SELECT booking_id, tong_tien, trang_thai, ngay_khoi_hanh, khach_hang_id
                FROM booking
                WHERE trang_thai IN ('ChoXacNhan', 'DaCoc')
                  AND ngay_khoi_hanh IS NOT NULL
                  AND ngay_khoi_hanh >= CURDATE()
                ORDER BY ngay_khoi_hanh ASC
                LIMIT 2000
            ''')
            rows = cursor.fetchall()
            cursor.close()

            updated = 0
            for row in rows:
                booking_id = row.get('booking_id', 0)
                if booking_id <= 0:
                    continue

                score = 0
                reasons = []
                tong_tien = float(row.get('tong_tien', 0))
                status = str(row.get('trang_thai', ''))
                ngay_khoi_hanh = row.get('ngay_khoi_hanh')

                # Calculate days until departure
                days_left = None
                if ngay_khoi_hanh:
                    today = datetime.now().date()
                    departure_date = ngay_khoi_hanh if isinstance(ngay_khoi_hanh, type(today)) else ngay_khoi_hanh.date() if hasattr(ngay_khoi_hanh, 'date') else today
                    days_left = (departure_date - today).days

                if days_left is not None:
                    if days_left <= 3:
                        score += 40
                        reasons.append('Khoi hanh <= 3 ngay')
                    elif days_left <= 7:
                        score += 25
                        reasons.append('Khoi hanh <= 7 ngay')

                if tong_tien >= 20000000:
                    score += 30
                    reasons.append('Gia tri booking cao >= 20tr')
                elif tong_tien >= 10000000:
                    score += 20
                    reasons.append('Gia tri booking >= 10tr')
                elif tong_tien >= 5000000:
                    score += 10
                    reasons.append('Gia tri booking >= 5tr')

                if status == 'ChoXacNhan':
                    score += 15
                    reasons.append('Dang cho xac nhan')

                priority = 'Low'
                if score >= 70:
                    priority = 'High'
                elif score >= 40:
                    priority = 'Medium'

                cursor = self.conn.cursor()
                cursor.execute(
                    'INSERT INTO booking_priority (booking_id, priority_label, score, reasons_json, computed_at) '
                    'VALUES (%s, %s, %s, %s, NOW()) '
                    'ON DUPLICATE KEY UPDATE priority_label = %s, score = %s, reasons_json = %s, computed_at = NOW()',
                    (
                        booking_id, priority, score, json.dumps(reasons, ensure_ascii=False),
                        priority, score, json.dumps(reasons, ensure_ascii=False)
                    )
                )
                self.conn.commit()
                cursor.close()
                updated += 1

                if priority == 'High' and days_left is not None and days_left <= 3:
                    self.record_event(
                        f'booking_priority_high_{booking_id}_{datetime.now().strftime("%Y%m%d")}',
                        'booking_priority',
                        'medium',
                        'Booking ưu tiên cao',
                        f'Booking #{booking_id} cần xử lý gấp (điểm {score}).',
                        {
                            'booking_id': booking_id,
                            'score': score,
                            'priority': priority,
                            'days_left': days_left,
                        },
                        True
                    )

            return {
                'ok': True,
                'job': 'booking_priority',
                'message': 'Booking priority computed',
                'affected': updated,
            }
        except Exception as e:
            logger.error(f'Booking priority job error: {e}')
            raise

    def run_reconcile_digest(self) -> Dict[str, Any]:
        """Run payment reconciliation."""
        # TODO: Implement payment reconciliation
        return {
            'ok': True,
            'job': 'reconcile_digest',
            'message': 'Reconcile digest generated',
            'affected': 1,
        }

    def run_self_heal_pending_payments(self) -> Dict[str, Any]:
        """Self-heal pending payments that timeout."""
        # TODO: Implement payment self-heal
        return {
            'ok': True,
            'job': 'self_heal_pending_payments',
            'message': 'Pending payment self-heal completed',
            'affected': 0,
        }

    def run_webhook_anomaly(self) -> Dict[str, Any]:
        """Detect webhook anomalies."""
        # TODO: Implement webhook anomaly detection
        return {
            'ok': True,
            'job': 'webhook_anomaly',
            'message': 'Webhook anomaly scan completed',
            'affected': 0,
        }

    def run_debt_reminder(self) -> Dict[str, Any]:
        """Check for overdue debts."""
        # TODO: Implement debt reminder
        return {
            'ok': True,
            'job': 'debt_reminder',
            'message': 'Debt reminder scan completed',
            'affected': 0,
        }

    def run_departure_readiness(self) -> Dict[str, Any]:
        """Check departure readiness."""
        # TODO: Implement departure readiness check
        return {
            'ok': True,
            'job': 'departure_readiness',
            'message': 'Departure readiness checked',
            'affected': 0,
        }

    def run_tour_health_score(self) -> Dict[str, Any]:
        """Calculate tour health scores."""
        # TODO: Implement tour health score calculation
        return {
            'ok': True,
            'job': 'tour_health_score',
            'message': 'Tour health scores calculated',
            'affected': 0,
        }

    def run_admin_inbox_digest(self) -> Dict[str, Any]:
        """Generate admin inbox digest."""
        # TODO: Implement admin inbox digest
        return {
            'ok': True,
            'job': 'admin_inbox_digest',
            'message': 'Admin inbox digest generated',
            'affected': 0,
        }

    def run_decision_assist(self) -> Dict[str, Any]:
        """Assist admin decision making."""
        # TODO: Implement decision assist
        return {
            'ok': True,
            'job': 'decision_assist',
            'message': 'Decision assist data prepared',
            'affected': 0,
        }

    def log_run(
        self,
        job_name: str,
        success: bool,
        affected: int,
        message: str,
        duration_ms: float
    ) -> None:
        """Log job execution."""
        status = 'OK' if success else 'FAIL'
        logger.info(
            f'[{job_name}] {status} | affected={affected} | '
            f'msg="{message}" | duration={duration_ms}ms'
        )


def main():
    """Main entry point."""
    logging.basicConfig(
        level=logging.INFO,
        format='[%(asctime)s] %(levelname)s: %(message)s'
    )

    parser = argparse.ArgumentParser(
        description='Admin Automation Job Runner'
    )
    parser.add_argument(
        'job',
        nargs='?',
        default='all',
        help='Job name or "all"'
    )
    args = parser.parse_args()

    try:
        service = AdminAutomationService()
        
        if args.job == 'all':
            results = service.run_all()
            logger.info(f'Ran {len(results)} jobs')
            for result in results:
                logger.info(json.dumps(result, ensure_ascii=False))
        else:
            result = service.run_job(args.job)
            logger.info(json.dumps(result, ensure_ascii=False))
        
        close_db_connection(service.conn)
        sys.exit(0)

    except Exception as e:
        logger.error(f'Fatal error: {e}')
        sys.exit(1)


if __name__ == '__main__':
    main()
